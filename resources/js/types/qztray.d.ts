declare module "qz-tray" {
  export interface PrinterOptions {
    name: string;
  }

  export interface Config {
    type: "printer";
    options: PrinterOptions;
  }

  export interface RawPrintData {
    type: "raw" | "pixel" | "html" | "pdf";
    format?: string;
    data: string;
  }

  export function connect(config?: any): Promise<void>;
  export function disconnect(): Promise<void>;
  export function getVersion(): Promise<string>;

  export namespace printers {
    function getDefault(): Promise<string>;
    function find(printer: string): Promise<string>;
    function list(): Promise<string[]>;
  }

  export function print(config: Config, data: RawPrintData[]): Promise<any>;

  export namespace api {
    function setPromiseType<T>(promiseType: any): void;
  }

  export namespace security {
    function setCertificatePromise(
      fn: (resolve: (cert: string) => void, reject: (err?: any) => void) => void
    ): void;

    function setSignaturePromise(
      fn: (toSign: string) => (resolve: (sig: string) => void, reject: (err?: any) => void) => void
    ): void;
  }

  const qz: {
    connect: typeof connect;
    disconnect: typeof disconnect;
    getVersion: typeof getVersion;
    printers: typeof printers;
    print: typeof print;
    api: typeof api;
    security: typeof security;
  };

  export default qz;
}