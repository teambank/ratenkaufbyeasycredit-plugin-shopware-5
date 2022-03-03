export function formatAmount(amount) {
  if (isNaN(amount)) {
    return '';
  }
  return Number(Math.round(Number(amount.toString() + 'e2')).toString() + 'e-2').toFixed(2).replace('.', ',');
}
export function formatCurrency(amount) {
  return formatAmount(amount) + ' €';
}
export function formatDate(dateString) {
  if (dateString) {
    return new Date(dateString).toLocaleDateString('de-DE', { year: 'numeric', month: '2-digit', day: '2-digit' });
  }
}
export function formatDatetime(dateString) {
  if (dateString) {
    return new Date(dateString).toLocaleDateString('de-DE', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
  }
}
export function fetchInstallmentPlans(webshopId, amount) {
  let uri = 'https://ratenkauf.easycredit.de/api/ratenrechner/v3/webshop/{{webshopId}}/installmentplans'
    .replace('{{webshopId}}', webshopId);
  const options = {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json; charset=utf-8'
    },
    body: JSON.stringify({
      "articles": [{
          "identifier": "single",
          "price": amount
        }]
    })
  };
  return fetch(uri, options)
    .then((response) => {
    if (response.ok) {
      return response.json();
    }
    return Promise.reject(response);
  })
    .then((response) => {
    return response;
  });
}
export function fetchAgreement(webshopId) {
  return fetch('https://ratenkauf.easycredit.de/api/payment/v3/webshop/' + webshopId).then((response) => {
    if (response.ok) {
      return response.json();
    }
    return Promise.reject(response);
  });
}
const defaultConfig = {
  request_config: {
    headers: {
      'Content-Type': 'application/json; charset=utf-8',
      'Authorization': 'Basic ' + btoa('2.de.7387.2:T:8:dkLSap1GYX49Xl?KzqU%8XYk#vEg')
    }
  },
  endpoints: {
    get: 'https://partner.easycredit-ratenkauf.de/api/merchant/v3/transaction/{transactionId}',
    capture: 'https://partner.easycredit-ratenkauf.de/api/merchant/v3/transaction/{transactionId}/capture',
    refund: 'https://partner.easycredit-ratenkauf.de/api/merchant/v3/transaction/{transactionId}/refund'
  }
};
function getConfig() {
  if (window && typeof window.ratenkaufbyeasycreditOrderManagementConfig !== 'undefined') {
    return window.ratenkaufbyeasycreditOrderManagementConfig;
  }
  return defaultConfig;
}
function getOptions(opts) {
  return Object.assign(Object.assign({}, getConfig().request_config), opts);
}
export async function fetchTransaction(txId) {
  if (txId === '') {
    return Promise.reject();
  }
  let uri = getConfig().endpoints.get.replace('{transactionId}', txId);
  return fetch(uri, getOptions({
    method: 'GET'
  }))
    .then((response) => {
    if (response.ok) {
      return response.json();
    }
    return Promise.reject(response);
  })
    .then((response) => {
    return response;
  });
}
export async function captureTransaction(txId, data) {
  let uri = getConfig().endpoints.capture.replace('{transactionId}', txId);
  return fetch(uri, getOptions({
    method: 'POST',
    body: JSON.stringify(data)
  }))
    .then((response) => {
    if (response.ok) {
      return true;
    }
    return Promise.reject(response);
  });
}
export async function refundTransaction(txId, data) {
  let uri = getConfig().endpoints.refund.replace('{transactionId}', txId);
  return fetch(uri, getOptions({
    method: 'POST',
    body: JSON.stringify(data)
  }))
    .then((response) => {
    if (response.ok) {
      return true;
    }
    return Promise.reject(response);
  });
}
