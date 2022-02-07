<template>
  <div class="app-container">

    <el-tabs value="first">
      <el-tab-pane label="Listes des clients" name="first">
        <el-table
          v-loading.fullscreen.lock="loading"
          v-if="customers"
          :data="customers"
          element-loading-text="Chargement"
          border
          fit
          highlight-current-row>

          <el-table-column
            prop="email"
            label="Email"
            sortable
            column-key="email"
          />

          <el-table-column
            prop="name"
            label="Name"
            sortable
            column-key="name"
          />


          <el-table-column
            label="Actions">
            <template slot-scope="scope">

              <el-button size="mini" @click="handleExport(scope.row)">
                Règles
              </el-button>

            </template>
          </el-table-column>

        </el-table>

      </el-tab-pane>
      <el-tab-pane label="Créer un client" name="second">

        <el-form ref="form" :model="form" label-width="120px">
          <el-form-item label="Nom - Prénom">
            <el-input v-model="form.name"></el-input>
          </el-form-item>
          <el-form-item label="Email">
            <el-input v-model="form.email"></el-input>
          </el-form-item>

          <el-form-item>
            <el-button type="primary" @click="onSubmit">Créer</el-button>
          </el-form-item>
        </el-form>

      </el-tab-pane>
    </el-tabs>


  </div>
</template>

<script>
  import { fetchCustomers, createCustomer } from '@/api/article'
  import { mapGetters } from 'vuex'

  export default {
    name: 'Customers',
    data() {
      return {
        customers: null,
        loading: true,
        form: {
          name: null,
          email: null
        }
      }
    },
    computed: {
      ...mapGetters([
        'token'
      ])
    },

    mounted() {
      this.fetchCustomers()
    },


    methods: {

      onSubmit () {
        this.loading = true

        createCustomer(this.form).then(response => {

          this.$message({
            message: 'Client ajouté',
            type: 'info'
          })
          this.form.email = null
          this.form.name = null
          this.loading = false
          this.fetchCustomers()
        }).catch(err => {
          this.$message({
            message: 'Impossible de créer le compte.',
            type: 'warning'
          })
          this.loading = false
        })
      },


      handleExport(row) {

        this.$router.push({ name: 'Rules', params: { userId: row.id } })
      },

      fetchCustomers() {
        fetchCustomers().then(response => {
          this.customers = response.data
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
