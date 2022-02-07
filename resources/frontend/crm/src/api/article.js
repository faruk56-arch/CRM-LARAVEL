import request from '@/utils/request'

export function fetchList(query, id, filter) {
  return request({
    url: `/landings/data/${id}` + filter,
    method: 'get',
    params: query
  })
}

export function fetchListDep(query, id, filter, date, date_2, zone_climatique) {
  return request({
    url: `/landings/data/departement/${id}/${filter}/${date}/${date_2}/${zone_climatique}`,
    method: 'get',
    params: query
  })
}

export function fetchProductionsRegion(category, query) {
  return request({
    url: `/productions/${category}`,
    method: 'get',
    params: query
  })
}

export function fetchGetProductionsRegion(category, query) {
  return request({
    url: `/productions/${category}/regions/data`,
    method: 'post',
    params: query
  })
}

export function postColor(category, query) {
  return request({
    url: `/productions/${category}/change_color`,
    method: 'post',
    params: query
  })
}


export function postTextNote(category, region, query) {
  return request({
    url: `/productions/${category}/${region}/change_text`,
    method: 'post',
    params: query
  })
}

export function fetchLanding(id) {
  return request({
    url: `/landings/${id}}`,
    method: 'get'
  })
}

export function putList(query) {
  const data = query
  return request({
    url: '/landings/data',
    method: 'post',
    data
  })
}


export function convertLead(query) {
  const data = query
  return request({
    url: '/landings/convert/leads',
    method: 'post',
    data
  })
}


export function deleteList(query) {
  const data = query
  return request({
    url: '/landings/data',
    method: 'delete',
    data
  })
}


export function postExport(query, id) {
  const data = query
  return request({
    url: `/landings/data/${id}/export`,
    method: 'post',
    data
  })
}

export function postImport(query, id) {
  const data = query;
  return request({
    url: `/landings/${id}/import/excel`,
    method: 'post',
    data
  })
}

export function fetchUsers() {
  return request({
    url: '/users',
    method: 'get'
  })

}

export function roleUser(id) {
  return request({
    url: '/users/admin/' + id,
    method: 'post'
  })
}

export function deleteUser(id) {
  return request({
    url: '/users/' + id,
    method: 'delete'
  })
}

export function createUser(data) {
  return request({
    url: '/auth/register',
    method: 'post',
    data
  })
}

export function getExports(data) {
  return request({
    url: '/exports',
    method: 'get',
    params: data
  })
}

export function fetchCustomers() {
  return request({
    url: '/customers',
    method: 'get'
  })
}

export function createCustomer(data) {
  return request({
    url: '/customers',
    method: 'post',
    data
  })
}

export function fetchAllLandings() {
  return request({
    url: '/all/landings',
    method: 'get'
  })
}


export function createRules(data) {
  return request({
    url: '/customers/rules',
    method: 'post',
    data
  })
}

export function deleteRule(id) {
  return request({
    url: '/customers/rules/' + id,
    method: 'delete'
  })
}


export function fetchRules(customer_id) {
  if (customer_id != 0) {
    return request({
      url: '/customers/rules',
      method: 'get',
      params: {customer_id: customer_id}
    })
  } else {
    return request({
      url: '/customers/rules',
      method: 'get'
    })
  }
}


export function fetchRapports(rule_id) {

    return request({
      url: '/rapports/rules/' + rule_id,
      method: 'get'
    })

}

export function exportInsert(id) {
  return request({
    url: '/exports/insert/'+id,
    method: 'post'
  })
}

export function generateBigImport(params) {
  return request({
    url: '/generate_big_export',
    method: 'post',
    params: params
  })
}

export function fetchCampaigns(id) {
  return request({
    url: '/taboola/campaigns/' + id,
    method: 'get'
  })
}

export function fetchAccounts() {
  return request({
    url: '/taboola/accounts',
    method: 'get'
  })
}

export function fetchPostalCodes(account_id, id) {
  return request({
    url: '/taboola/campaigns/' + account_id + '/' + id + '/codes',
    method: 'get'
  })
}

export function postPostalCode(account_id, id, formData) {
  const data = formData
  return request({
    url: '/taboola/campaigns/' + account_id + '/' + id + '/codes',
    method: 'post',
    headers: {
      'Content-Type': 'multipart/form-data'
    },
    data
  })
}
