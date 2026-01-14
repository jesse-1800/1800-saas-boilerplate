import {defineStore} from 'pinia';
import type {GlobalStateTypes} from '@/types/StoreTypes';

export const GlobalStore = defineStore('globals', {
  state: (): GlobalStateTypes => ({

  }),
  persist: {
    pick: [],
  },
  actions: {
  }
});
