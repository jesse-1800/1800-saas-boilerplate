import axios from "axios";
import {GlobalStore} from "@/stores/globals.ts";

export const UccServer = (token: any) => {
  const instance = axios.create({
    baseURL: "/",
    headers: {
      "X-Authorization": `Bearer ${token}`,
    },
  });

  const {ShowError} = GlobalStore();

  instance.interceptors.response.use(
    (response) => {
      if (response.data?.result === false) {
        ShowError(response.data.message || "Unknown error");
        return Promise.reject(new Error(response.data.message));
      }
      return response;
    },
    (error) => {
      const err_msg = error?.response?.data?.message;
      ShowError(err_msg ? err_msg : "A network or server error occurred");
      return Promise.reject(error);
    }
  );

  return instance;
};
