import axios from 'axios'

const CatalogServer = axios.create({
  baseURL: import.meta.env.VITE_CATALOG_BASE_URL,
  headers: {
    'X-Api-Key': import.meta.env.VITE_CATALOG_API_KEY,
  },
});

CatalogServer.interceptors.response.use(
  async (response) => {
    if (response?.data?.result === false) {
      const {GlobalStore} = await import('@/stores/globals');
      const {ShowError,LogError} = GlobalStore();
      const msg = response.data?.errors || response.data?.message || 'An error occurred';
      ShowError(msg);
      LogError(msg);
      return Promise.reject(new Error(msg));
    }
    return response;
  },
  async (error) => {
    const {GlobalStore} = await import('@/stores/globals')
    const {ShowError,LogError} = GlobalStore();
    ShowError('Network error');
    LogError('Network error');
    return Promise.reject(error);
  }
)

export default CatalogServer;
