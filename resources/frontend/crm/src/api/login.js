import request from '@/utils/request'

export function loginByUsername(username, password, code) {
  const data = {
    email: username,
    password,
    code
  }
  console.log('bef log: ', data)
  return request({
    url: '/auth',
    method: 'post',
    data
  })
}

export function logout() {
  return request({
    url: '/login/logout',
    method: 'post'
  })
}

export function getUserInfo(token) {
  return request({
    url: '/user/info',
    method: 'get',
    params: { token }
  })
}

