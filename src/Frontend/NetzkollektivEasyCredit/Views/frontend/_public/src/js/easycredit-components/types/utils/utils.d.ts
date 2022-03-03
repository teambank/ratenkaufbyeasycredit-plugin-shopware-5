export declare function formatAmount(amount: number): string;
export declare function formatCurrency(amount: number): string;
export declare function formatDate(dateString: any): string;
export declare function formatDatetime(dateString: any): string;
export declare function fetchInstallmentPlans(webshopId: string, amount: number): Promise<any>;
export declare function fetchAgreement(webshopId: string): Promise<any>;
export declare function fetchTransaction(txId: string): Promise<any>;
export declare function captureTransaction(txId: string, data: any): Promise<boolean>;
export declare function refundTransaction(txId: string, data: any): Promise<boolean>;
