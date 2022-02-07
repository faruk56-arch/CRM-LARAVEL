import request from '@/utils/request'

export function fetchLandings() {
  return request({
    url: '/landings',
    method: 'get'
  })
}

export function fetchLandings_Facebook() {
  return request({
    url: '/landings_facebook',
    method: 'get'
  })
}

export function fetchUser() {
  return request({
    url: '/me',
    method: 'get'
  })
}
