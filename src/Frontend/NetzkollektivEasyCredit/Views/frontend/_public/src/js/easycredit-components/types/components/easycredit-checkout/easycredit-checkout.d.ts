export declare class EasycreditCheckout {
  isActive: boolean;
  amount: number;
  webshopId: string;
  alert: string;
  paymentPlan: string;
  askForPrefix: boolean;
  privacyApprovalForm: string;
  privacyCheckboxChecked: boolean;
  totals: {
    interest: number;
    total: number;
  };
  installments: any;
  selectedInstallment: {
    totalInterest: number;
    totalValue: number;
    numberOfInstallments: number;
  };
  example: any;
  submitDisabled: boolean;
  modal: HTMLEasycreditModalElement;
  selectedInstallmentHandler(e: any): void;
  componentWillLoad(): Promise<void>;
  el: HTMLElement;
  onSubmit(): void;
  getPaymentPlan(): any;
  getPaymentPlanFragment(): any;
  getCheckoutFragment(): any;
  getPrefixFragment(): void;
  handleCheckbox(e: any): void;
  getPrivacyFragment(): any;
  getModalFragment(): any[];
  render(): any[];
}
