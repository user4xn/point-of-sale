<?php

use App\Services\OwnerSummary;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        test()->markTestSkipped('The pdo_sqlite extension is required for isolated in-memory tests.');
    }
    $this->originalResolver = Model::getConnectionResolver();
    $this->db = new Manager;
    $this->db->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
    $this->db->bootEloquent();
    $schema = $this->db->getConnection()->getSchemaBuilder();
    $schema->create('transactions', function (Blueprint $table) {
        $table->id();
        $table->string('status');
        $table->decimal('total_price');
        $table->decimal('discount');
        $table->dateTime('created_at');
    });
    $schema->create('expenditures', function (Blueprint $table) {
        $table->id();
        $table->date('date');
        $table->decimal('amount');
    });
    $schema->create('products', function (Blueprint $table) {
        $table->id();
        $table->decimal('stock');
        $table->decimal('purchase_price');
    });
    Carbon::setTestNow('2025-03-31 12:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
    if (isset($this->db)) {
        if ($this->originalResolver) {
            Model::setConnectionResolver($this->originalResolver);
        } else {
            Model::unsetConnectionResolver();
        }
        $this->db->getConnection()->disconnect();
    }
    Mockery::close();
    Auth::clearResolvedInstances();
});

it('summarizes paid net sales with clamped comparison and zero filled days', function () {
    Auth::shouldReceive('user')->once()->andReturn((object) ['role' => 'admin']);
    foreach ([
        ['paid', 120, 20, '2025-03-01 00:00:00'],
        ['paid', 220, 20, '2025-03-31 12:00:00'],
        ['paid', 999, 0, '2025-03-31 12:00:01'],
        ['cancelled', 999, 0, '2025-03-30 10:00:00'],
        ['paid', 60, 10, '2025-02-28 12:00:00'],
        ['paid', 999, 0, '2025-02-28 12:00:01'],
        ['paid', 999, 0, '2024-03-01 00:00:00'],
    ] as [$status, $total, $discount, $date]) {
        $this->db->table('transactions')->insert(['status' => $status, 'total_price' => $total, 'discount' => $discount, 'created_at' => $date]);
    }
    $this->db->table('expenditures')->insert([
        ['date' => '2025-03-31', 'amount' => 30],
        ['date' => '2025-02-28', 'amount' => 900],
        ['date' => '2025-04-01', 'amount' => 900],
    ]);
    $this->db->table('products')->insert(['stock' => 3, 'purchase_price' => 40]);
    $summary = (new OwnerSummary)->metrics();
    expect($summary['net_sales'])->toBe(300.0)
        ->and($summary['transactions'])->toBe(2)
        ->and($summary['average_order_value'])->toBe(150.0)
        ->and($summary['previous_net_sales'])->toBe(50.0)
        ->and($summary['previous_end'])->toBe('2025-02-28 12:00:00')
        ->and($summary['change_percent'])->toBe(500.0)
        ->and($summary['expenses'])->toBe(30.0)
        ->and($summary['estimated_stock_value'])->toBe(120.0)
        ->and($summary['trend'])->toHaveCount(14)
        ->and($summary['trend'][0])->toBe(['date' => '2025-03-18', 'net_sales' => 0.0])
        ->and($summary['trend'][13])->toBe(['date' => '2025-03-31', 'net_sales' => 200.0]);
});

it('returns finite empty metrics without a comparison percentage', function () {
    Auth::shouldReceive('user')->once()->andReturn((object) ['role' => 'admin']);
    $summary = (new OwnerSummary)->metrics();
    expect($summary['change_percent'])->toBeNull()
        ->and($summary['net_sales'])->toBe(0.0)
        ->and($summary['average_order_value'])->toBe(0)
        ->and(array_sum(array_column($summary['trend'], 'net_sales')))->toBe(0.0);
});

it('rejects non admins before querying summary data', function () {
    Auth::shouldReceive('user')->once()->andReturn((object) ['role' => 'cashier']);
    $this->db->getConnection()->enableQueryLog();
    try {
        (new OwnerSummary)->metrics();
        test()->fail('Expected forbidden response');
    } catch (Symfony\Component\HttpKernel\Exception\HttpException $exception) {
        expect($exception->getStatusCode())->toBe(403)
            ->and($this->db->getConnection()->getQueryLog())->toBe([]);
    }
});
