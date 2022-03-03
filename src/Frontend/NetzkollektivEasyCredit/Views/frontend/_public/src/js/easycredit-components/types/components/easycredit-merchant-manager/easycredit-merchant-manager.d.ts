export declare class EasycreditMerchantStatusWidget {
  tx: any;
  loading: boolean;
  status: any;
  submitDisabled: boolean;
  alert: {
    message: String;
    type: String;
  };
  progressItems: {
    created: String;
    status: String;
    type: String;
    uuid: String;
    message: String;
  }[];
  trackingNumber: any;
  amount: any;
  txId: string;
  date: string;
  alertElement: HTMLElement;
  typeLabels: {
    ORDER: string;
    CAPTURE: string;
    REFUND: string;
  };
  componentWillLoad(): Promise<void>;
  loadTransaction(): void;
  youngerThanOneDay(): boolean;
  orderAmount(): string;
  canShip(): boolean;
  canRefund(): boolean;
  showAlert(alert: any): void;
  updateTransaction(): Promise<void>;
  getProgressBarFragment(): any[];
  getInfoFragment(): any[];
  getAlertFragment(): any;
  getActionsFragment(): any[];
  getNotAvailableFragment(): any[];
  render(): any[];
}
