import { asyncRouterMap, constantRouterMap } from '@/router'
import { fetchLandings, fetchLandings_Facebook } from '@/api/SideMenuLandings'

/**
 * 通过meta.role判断是否与当前用户权限匹配
 * @param roles
 * @param route
 */
function hasPermission(roles, route) {
  if (route.meta && route.meta.roles) {
    return roles.some(role => route.meta.roles.includes(role))
  } else {
    return true
  }
}

/**
 * 递归过滤异步路由表，返回符合用户角色权限的路由表
 * @param routes asyncRouterMap
 * @param roles
 */
function filterAsyncRouter(routes, roles) {
  const res = []

  routes.forEach(route => {
    const tmp = { ...route }
    if (hasPermission(roles, tmp)) {
      if (tmp.children) {
        tmp.children = filterAsyncRouter(tmp.children, roles)
      }
      res.push(tmp)
    }
  })

  return res
}

function roadsGenerators(i, res) {
  asyncRouterMap[i].children = []
  for (const l of res.data) {
    asyncRouterMap[i].children.push({
      path: l.name.replace(' ', ''),
      redirect: '',
      component: () => import('@/views/excel/selectExcel'),
      props: { landingName: l.id },
      name: l.name,
      meta: { title: l.name }
    })
  }
}

const permission = {
  state: {
    routers: constantRouterMap,
    addRouters: []
  },
  mutations: {
    SET_ROUTERS: (state, routers) => {
      state.addRouters = routers
      state.routers = constantRouterMap.concat(routers)
    }
  },
  actions: {
    GenerateRoutes({ commit }, data) {
      return new Promise(resolve => {
        const { roles } = data
        let accessedRouters
        // console.log('asyncRouter: ', asyncRouterMap)

        fetchLandings().then((res) => {
          // console.log('LANDING LIST RESPONSE :', res)
          const index = asyncRouterMap.findIndex((elem) => {
            return elem.name === 'Landings'
          })
          roadsGenerators(index, res)
          return fetchLandings_Facebook()
        }).then((res) => {
          const index = asyncRouterMap.findIndex((elem) => {
            return elem.name === 'Landings_Facebook'
          })
          roadsGenerators(index, res)
          // console.log(index)
          // console.log(asyncRouterMap[index])

          // console.log(asyncRouterMap[index])
          // children: [
          //   {
          //     path: 'programmeSoleil',
          //     component: () => import('@/views/excel/selectExcel'),
          //     props: { landingName: 'programmesoleilcoms' },
          //     name: 'programmesoleilcoms',
          //     meta: { title: 'Programme Soleil' }
          //   },
          //   {
          //     path: 'transitionenergie',
          //     component: () => import('@/views/excel/selectExcel'),
          //     name: 'transitionenergiecoms',
          //     props: { landingName: 'transitionenergiecoms' },
          //     meta: { title: 'Transition energie' }
          //   }// TODO new landing  to sidebar
          // ]
        }).catch(() => {

        })
        if (roles.includes('admin')) {
          accessedRouters = asyncRouterMap
        } else {
          accessedRouters = filterAsyncRouter(asyncRouterMap, roles)
        }
        commit('SET_ROUTERS', accessedRouters)
        resolve()
      })
    }
  }
}

export default permission
