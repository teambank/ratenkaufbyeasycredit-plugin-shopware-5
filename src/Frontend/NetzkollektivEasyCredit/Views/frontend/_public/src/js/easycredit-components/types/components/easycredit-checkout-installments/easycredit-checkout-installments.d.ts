import { EventEmitter } from '../../stencil-public-runtime';
export declare class EasycreditCheckoutInstallments {
  showMoreButtonText: string;
  installments: any;
  rows: number;
  collapsed: boolean;
  collapsing: boolean;
  _installments: any;
  selectedInstallmentValue: number;
  selectedInstallmentHandler(e: any): void;
  parseInstallmentsProp(newValue: string): void;
  installmentsBase: HTMLElement;
  componentWillLoad(): Promise<void>;
  componentDidLoad(): void;
  selectedInstallment: EventEmitter<string>;
  selectFirstOption(): void;
  listBase(): any[];
  listExtended(): any[];
  listExtendedMaxHeight(): string;
  listClasses(cls: any): any;
  toggleList(): void;
  onInstallmentSelect(e: any): void;
  getInstallmentFragment(installment: any): any;
  getMoreListFragment(): any;
  render(): any;
}
