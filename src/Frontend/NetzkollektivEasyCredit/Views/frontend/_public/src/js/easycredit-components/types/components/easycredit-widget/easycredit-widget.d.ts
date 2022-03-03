export declare class EasycreditWidget {
  /**
   * Webshop Id
   */
  webshopId: string;
  /**
   * Financing Amount
   */
  amount: number;
  modal: HTMLEasycreditModalElement;
  installments: any;
  isValid: boolean;
  private getLinkText;
  private getInstallmentText;
  componentWillLoad(): void;
  getInstallmentPlan(): any;
  getMinimumInstallment(): any;
  getRatenkaufIcon(): any;
  render(): any[];
}
