<template>
  <div class="app-container" v-loading.fullscreen.lock="loading">

      <h2>Modifier une campagne Taboola</h2>

    <el-form>

      <el-form-item label="Sélectionner un compte">
        <el-select v-model="account_id" placeholder="Séléctionner un compte" v-if="accounts">
          <el-option :label="account.name" :value="account.account_id" v-for="account in accounts" v-bind:key="account.id"></el-option>
        </el-select>
      </el-form-item>


      <el-form-item label="Sélectionner une campagne" v-if="account_id && campaigns">
        <el-select v-model="campaign_id" placeholder="Séléctionner une campagne" v-if="campaigns">
          <el-option :label="campaign.name + ' (' + campaign.advertiser_id + ')'" :value="campaign.id" v-bind:key="campaign.id" v-for="campaign in campaigns"></el-option>
        </el-select>
      </el-form-item>

      <el-form-item label="Nodifier les parametres de target" v-if="account_id && campaign_id && postal_codes && type">
        <el-select v-model="type" placeholder="Select" ref="input_type">
          <el-option label="Tous les codes postaux" value="ALL" value-key="ALL"></el-option>
          <el-option label="Seulement les codes séléctionnés" value="INCLUDE" value-key="INCLUDE"></el-option>
        </el-select>
      </el-form-item>

      <el-form-item label="Sélectionner un fichier" v-if="account_id && campaign_id && postal_codes && type == 'INCLUDE'">
      <el-upload
        class="upload-demo"
        drag
        ref="upload"
        accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        action="https://jsonplaceholder.typicode.com/posts/"
        :auto-upload="false">
        <i class="el-icon-upload"></i>
        <div class="el-upload__text">Déposer les fichiers ici ou<em>cliquez pour envoyer</em></div>
        <div class="el-upload__tip" slot="tip">Fichiers xlsx</div>
      </el-upload>

      </el-form-item>

      <el-button type="success" @click="send" v-if="account_id && campaign_id && postal_codes" :loading="loading_button">Sauvegarder</el-button>


      <div v-if="campaign_id && postal_codes" style="width: 100%; height: auto;">

        <h4>Code postaux en production</h4>


        <div style="display: inline-block; margin-right: 10px;" v-show="type == 'INCLUDE'" v-for="item in postal_codes.collection" v-bind:key="item"> {{item}}, </div>
        <span v-show="type == 'ALL'">Tous</span>

      </div>
    </el-form>

  </div>
</template>

<script>
  import { fetchCampaigns, fetchAccounts, fetchPostalCodes, postPostalCode } from '@/api/article'
  import { mapGetters } from 'vuex'
  import jsonCode from './postal.json'

  export default {
    name: 'Taboola',
    data() {
      return {
        campaigns: null,
        accounts: null,
        loading: true,
        account_id: null,
        campaign_id: null,
        postal_codes: null,
        input: null,
        type: null,
        file: null,
        loading_button: false,
        codes: jsonCode
      }
    },
    computed: {
      ...mapGetters([
        'token'
      ])
    },

    mounted() {
      this.fetchAccounts()
    },

    watch: {
      account_id: function (val) {
        this.loading = true;
        this.campaigns = null;
        this.campaign_id = null;
        this.postal_codes = null;
        this.fetchCampaigns(val);
      },
      campaign_id: function (val) {
        this.postal_codes = null;
        this.loading = true;
        this.fetchPostalCodes(this.account_id, val);
      }
    },

    methods: {

      send() {
        this.loading_button = true;
        var file = document.getElementsByClassName('el-upload__input');
        let formData = new FormData();


        if (this.type == 'INCLUDE' && file[0] && file[0].files[0]) {
          formData.append('file', file[0].files[0]);
          formData.append('type', 'INCLUDE');
          postPostalCode(this.account_id, this.campaign_id, formData).then(response => {
            this.$message({
              message: 'Code postaux mis à jour',
              type: 'info'
            });
            this.loading_button = false;
            this.fetchPostalCodes(this.account_id, this.campaign_id);
          }).catch(err => {
            this.$message({
              message: 'Impossible de poster les données.',
              type: 'warning'
            });
            this.loading_button = false;
          });
        } else if (this.type == 'ALL') {
          formData.append('type', 'ALL');
          postPostalCode(this.account_id, this.campaign_id, formData).then(response => {
            this.$message({
              message: 'Code postaux mis à jour',
              type: 'info'
            });
            this.loading_button = false;
            this.fetchPostalCodes(this.account_id, this.campaign_id);
          }).catch(err => {
            this.$message({
              message: 'Impossible de poster les données.',
              type: 'warning'
            });
            this.loading_button = false;
          });
        } else {
          this.$message({
            message: 'Erreur',
            type: 'warning'
          });
          this.loading_button = false;
          return false;
        }

      },

      fetchCampaigns(value) {
        fetchCampaigns(value).then(response => {
          this.campaigns = response.data
          this.loading = false
        }).catch(err => {
          this.$message({
            message: 'Impossible de récupérer les données.',
            type: 'warning'
          });
          this.loading = false
        })
      },

      fetchAccounts() {
        fetchAccounts().then(response => {
          this.accounts = response.data
          this.loading = false
        }).catch(err => {
          this.$message({
            message: 'Impossible de récupérer les données.',
            type: 'warning'
          })
          this.loading = false
        })
      },

      fetchPostalCodes(value, val) {
        fetchPostalCodes(value, val).then(response => {
          this.postal_codes = response.data;
          this.type = response.data.type;
          this.loading = false
        }).catch(err => {
          this.$message({
            message: 'Impossible de récupérer les données.',
            type: 'warning'
          })
          this.loading = false
        })
      },
    }
  }
</script>
