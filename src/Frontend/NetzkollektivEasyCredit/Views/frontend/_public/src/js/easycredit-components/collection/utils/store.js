import { createStore } from "@stencil/store";
// import config from './config'
const { state } = createStore({
  loading: false,
  txIds: [],
  transactions: {}
});
/*
const loadTransactions = async (txIds) => {
  await fetch(config.getEndpoints().list, config.getRequestConfig()).then(async (result) => {
    const transactions = await result.json()
    if (transactions && Array.isArray(transactions)) {
      state.transactions = transactions
      state.loading = false
    }
  })
}

var timeout = null
onChange('txIds', (txIds) => {
  clearTimeout(timeout)
  timeout = setTimeout( async () => {
    await loadTransactions(txIds)
  }, 200)
});
*/
export default state;
