const LINE_WIDTH = 32; // untuk 58mm, kalau 80mm biasanya 48

function line(left: string, right: string = "") {
  const space = LINE_WIDTH - left.length - right.length;
  return left + " ".repeat(space > 0 ? space : 0) + right + "\n";
}

function divider() {
  return "-".repeat(LINE_WIDTH) + "\n";
}

function formatCurrency(num: number) {
  return "Rp" + num.toLocaleString("id-ID");
}

function formatFooter(template: string, data: any) {
  return template
    .replace(/\[store_name\]/g, data.store_name || "")
    .replace(/\[store_address\]/g, data.store_address || "")
    .replace(/\[store_contact\]/g, data.store_contact || "");
}

export function buildReceipt(data: any) {
  let out = "";

  // Header toko
  out += data.store_name.toUpperCase() + "\n";
  out += data.store_address + "\n";
  out += "Telp: " + data.store_contact + "\n\n";

  out += divider();

  // Info transaksi
  out += line("No. Order", data.invoice);
  out += line("Tanggal", data.date);
  out += line("Kasir", data.cashier);
  out += divider();

  // Items
  data.items.forEach((item: any) => {
    out += line(item.name, `${item.qty} ${item.unit}`);
    out += line("   x " + formatCurrency(item.price), formatCurrency(item.subtotal));
  });

  out += divider();

  // Summary
  out += line("Subtotal", formatCurrency(data.total_price));
  out += line("Diskon", formatCurrency(data.discount));
  out += line("PPN", formatCurrency(data.tax));
  out += line("TOTAL", formatCurrency(data.grand_total));
  out += line("Tunai", formatCurrency(data.paid_amount));
  out += line("Kembali", formatCurrency(data.change_amount));

  out += divider();

  const footerText = formatFooter(data.footer, data);

  // Footer
  out += "*** " + footerText + " ***\n";
  out += "Terima Kasih & Sampai Jumpa\n\n\n";

  return out;
}