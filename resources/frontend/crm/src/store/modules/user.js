import { loginByUsername, logout, getUserInfo } from '@/api/login'
import { getToken, setToken, removeToken } from '@/utils/auth'
import { Message } from 'element-ui'

const user = {
  state: {
    user: '',
    status: '',
    code: '',
    token: getToken(),
    name: '',
    avatar: '',
    introduction: '',
    roles: [],
    setting: {
      articlePlatform: []
    }
  },

  mutations: {
    SET_CODE: (state, code) => {
      state.code = code
    },
    SET_TOKEN: (state, token) => {
      state.token = token
    },
    SET_INTRODUCTION: (state, introduction) => {
      state.introduction = introduction
    },
    SET_SETTING: (state, setting) => {
      state.setting = setting
    },
    SET_STATUS: (state, status) => {
      state.status = status
    },
    SET_NAME: (state, name) => {
      state.name = name
    },
    SET_AVATAR: (state, avatar) => {
      state.avatar = avatar
    },
    SET_ROLES: (state, roles) => {
      state.roles = roles
    },
    SET_USER: (state, user) => {
      state.userData = user
    }
  },

  actions: {
    // 用户名登录
    LoginByUsername({ commit }, userInfo) {
      const username = userInfo.username.trim()
      return new Promise((resolve, reject) => {
        loginByUsername(username, userInfo.password, userInfo.code).then(response => {
          const data = response.data

          commit('SET_TOKEN', data.token)
          commit('SET_USER', data)
          setToken(data.token)
          resolve()
        }).catch(error => {
          Message({
            message: 'Identification impossible',
            type: 'error',
            duration: 5 * 1000
          })
        })
      })
    },

    // 获取用户信息
    GetUserInfo: function({ commit, state }) {
      return new Promise((resolve, reject) => {
        // if (!state.userData) { reject('userData not set') }
        const response = state.userData

        // if (response) {
        // editor: {
        //   roles: ['editor'],
        //     token: 'editor',
        //     introduction: '我是编辑',
        //     avatar: 'https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif',
        //     name: 'Normal Editor'
        // ======================================
        // blocked: false
        // confirmed: true
        // email: "kevin@hono-agency.fr"
        // provider: "local"
        // role:
        //   description: "These users have all access in the project."
        // name: "Administrator"
        // type: "root"
        // __v: 0
        // _id: "5c17bd17a33435f482336e20"
        // __proto__: Object
        // username: "asmedia"
        // __v: 0
        // _id: "5c17bd78a33435f482336f04"
        const data = response

        data.roles = []
        // data.roles[0] = data.role.name === 'Administrator' ? 'admin' : 'editor' TODO  if add Permission
        data.roles[0] = 'admin'
        if (data.roles && data.roles.length > 0) { // 验证返回的roles是否是一个非空数组
          commit('SET_ROLES', data.roles)
        } else {
          reject('getInfo: roles must be a non-null array !')
        }

        // commit('SET_NAME', data.username)
        commit('SET_NAME', 'username')
        commit('SET_AVATAR', '') // TODO image Profil
        // commit('SET_INTRODUCTION', data.role.description)
        commit('SET_INTRODUCTION', 'Description')
        // console.log('hello', data)
        resolve(response)
        // } else {
        //   reject('No User Data')
        // }
        return
      })
    },

    // 第三方验证登录
    // LoginByThirdparty({ commit, state }, code) {
    //   return new Promise((resolve, reject) => {
    //     commit('SET_CODE', code)
    //     loginByThirdparty(state.status, state.email, state.code).then(response => {
    //       commit('SET_TOKEN', response.data.token)
    //       setToken(response.data.token)
    //       resolve()
    //     }).catch(error => {
    //       reject(error)
    //     })
    //   })
    // },

    // 登出
    LogOut({ commit, state }) {
      commit('SET_TOKEN', '')
      commit('SET_ROLES', [])
      removeToken()
      return []
    },

    // 前端 登出
    FedLogOut({ commit }) {
      return new Promise(resolve => {
        // console.log('FEDLOGOUT')
        commit('SET_TOKEN', '')
        removeToken()
        resolve()
      })
    },

    // 动态修改权限
    ChangeRoles({ commit, dispatch }, role) {
      return new Promise(resolve => {
        commit('SET_TOKEN', role)
        // console.log('WARNING  change  ROles')
        setToken(role)
        getUserInfo(role).then(response => {
          const data = response.data
          commit('SET_ROLES', data.roles)
          commit('SET_NAME', data.name)
          commit('SET_AVATAR', data.avatar)
          commit('SET_INTRODUCTION', data.introduction)
          dispatch('GenerateRoutes', data) // 动态修改权限后 重绘侧边菜单
          resolve()
        })
      })
    }
  }
}

export default user
