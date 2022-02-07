import axios from 'axios'
import { Message } from 'element-ui'
// import store from '@/store'
import { getToken } from '@/utils/auth'
import store from '../store'
require('dotenv').config();


// create an axios instance
const service = axios.create({
  // baseURL: 'http://api-asmedia:809/api',
  baseURL: '/api',
  timeout: 9000000 // request timeout
});


// request interceptor
service.interceptors.request.use(
  config => {
    // Do something before request is sent
    if (getToken()) {
      config.headers['Authorization'] = `Bearer ${getToken()}`
    }
    return config
  },
  error => {
    Promise.reject(error)
  }
)

// response interceptor
service.interceptors.response.use(
  response => response,
  error => {
    if (error.response.data.error === 'token_expired' || error.response.data.error === 'token_not_provided') {

      store.dispatch('FedLogOut').then(() => {
        Message.error('Token Expired, please login again')
      })
    }
    return Promise.reject(error)
  }
)

export default service
