export declare class EasycreditModal {
  element: HTMLElement;
  loading: boolean;
  loadingMessage: string;
  show: boolean;
  isOpen: boolean;
  watchShowHandler(shown: boolean): void;
  close(): Promise<void>;
  open(): Promise<void>;
  toggle(): Promise<void>;
  getCloseIcon(): any;
  render(): any[];
}
