<template>
  <el-scrollbar wrap-class="scrollbar-wrapper">
    <el-menu
      :show-timeout="200"
      :collapse="isCollapse"
      mode="vertical"
      background-color="#304156"
      text-color="#bfcbd9"
      active-text-color="#bfcbd9"
    >

      <el-submenu index="1">
        <template slot="title">
          <i class="el-icon-tickets"/>
          <span>Landings</span>
        </template>
        <router-link v-for="(item, key) in landings" :key="key" :to="{ name: 'Leadview', params: { id: [item.id].toString() }}">
          <el-menu-item index="1-1">{{ item.name }}</el-menu-item>
        </router-link>

      </el-submenu>

      <el-submenu index="2">
        <template slot="title">
          <i class="el-icon-news"/>
          <span>Facebook</span>
        </template>

        <router-link v-for="(item, key) in landingsFacebook" :key="key" :to="{ name: 'Leadview', params: { id: [item.id].toString() }}">
          <el-menu-item index="1-1">{{ item.name }}</el-menu-item>
        </router-link>

      </el-submenu>

      <el-submenu index="3" v-if="user">
        <template slot="title">
          <i class="el-icon-share"/>
          <span>Production</span>
        </template>

        <router-link to="/production/pac" tag="li">
          <el-menu-item index="3-1">PAC</el-menu-item>
        </router-link>

        <router-link to="/production/pv" tag="li">
          <el-menu-item index="3-2">PV</el-menu-item>
        </router-link>
        <router-link to="/production/other" tag="li">
          <el-menu-item index="3-2">AUTRE</el-menu-item>
        </router-link>
      </el-submenu>

      <el-submenu index="3" v-if="user && user.admin">
        <template slot="title">
          <i class="el-icon-sort"/>
          <span>Lives</span>
        </template>

        <router-link to="/customers" tag="li">
          <el-menu-item index="3-1">Clients</el-menu-item>
        </router-link>

        <router-link to="/customers/rules/0" tag="li">
          <el-menu-item index="3-2">Règles</el-menu-item>
        </router-link>
      </el-submenu>

      <el-submenu index="4" v-if="user && user.admin">
        <template slot="title">
          <i class="el-icon-setting"/>
          <span>Administration</span>
        </template>

        <router-link to="/users" tag="li" v-if="user && user.admin">
          <el-menu-item index="1-1">Gestion des comptes</el-menu-item>
        </router-link>

        <router-link to="/exports/0" tag="li" v-if="user && user.admin">
          <el-menu-item index="1-1">Gestion des exports</el-menu-item>
        </router-link>

        <router-link to="/taboola" tag="li" v-if="user && user.admin">
          <el-menu-item index="1-1">Taboola</el-menu-item>
        </router-link>


      </el-submenu>

    </el-menu>
  </el-scrollbar>
</template>

<script>
import { mapGetters } from 'vuex'
import AppLink from './Link'
import { fetchLandings, fetchLandings_Facebook, fetchUser } from '@/api/SideMenuLandings'

export default {
  components: { AppLink },
  data() {
    return {
      landings: [],
      user: [],
      landingsFacebook: []
    }
  },
  computed: {
    ...mapGetters([
      'permission_routers',
      'sidebar'
    ]),
    isCollapse() {
      return !this.sidebar.opened
    }
  },

  created() {
    fetchLandings_Facebook().then(response => {
      this.landingsFacebook = response.data
    })

    fetchLandings().then(response => {
      this.landings = response.data
    })

    fetchUser().then(response => {
      this.user = response.data
    })
  }
}
</script>
