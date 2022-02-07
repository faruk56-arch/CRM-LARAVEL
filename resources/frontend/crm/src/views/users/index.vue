<template>
  <div class="app-container">

    <el-tabs value="first">
      <el-tab-pane label="Listes" name="first">
        <el-table
          v-loading.fullscreen.lock="loading"
          v-if="users"
          :data="users"
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
            prop="admin"
            label="Admin"
            sortable
            column-key="admin"
          />

          <el-table-column label="Exports">

            <template slot-scope="scope">
              <el-button size="mini" @click="handleExport(scope.row)">
                Exports
              </el-button>

            </template>


          </el-table-column>

          <el-table-column
            label="Actions">
            <template slot-scope="scope">
              <el-button
                size="mini"
                v-show="!scope.row.admin"
                @click="handleAdmin(scope.$index, scope.row)">Attributer rôle admin</el-button>
              <el-button
                size="mini"
                v-show="scope.row.admin"
                @click="handleAdmin(scope.$index, scope.row)">Attributer rôle normal</el-button>
              <el-button
                size="mini"
                type="danger"
                @click="handleDelete(scope.$index, scope.row)">Supprimer</el-button>
            </template>
          </el-table-column>

        </el-table>

      </el-tab-pane>
      <el-tab-pane label="Créer un compte" name="second">

        <el-form ref="form" :model="form" label-width="120px">
          <el-form-item label="Nom - Prénom">
            <el-input v-model="form.name"></el-input>
          </el-form-item>
          <el-form-item label="Email">
            <el-input v-model="form.email"></el-input>
          </el-form-item>

          <el-form-item>
            <el-button type="primary" @click="onSubmit">Create</el-button>
          </el-form-item>
        </el-form>

      </el-tab-pane>
    </el-tabs>

    <el-dialog
      title="Confirmation"
      :visible.sync="dialogVisible"
      width="30%">
      <span>Souhaitez-vous vraiment supprimer cet utilisateur ?</span>
      <span slot="footer" class="dialog-footer">
      <el-button @click="dialogVisible = false">Annuler</el-button>
      <el-button type="danger" @click="handleConfirmedDelete">Supprimer</el-button>
    </span>
    </el-dialog>

  </div>
</template>

<script>
  import { fetchUsers, roleUser, deleteUser, createUser } from '@/api/article'
  import { mapGetters } from 'vuex'

  export default {
    name: 'Users',
    data() {
      return {
        users: null,
        loading: true,
        handleDeleteId: null,
        dialogVisible: false,
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
      this.fetchUsers()
    },


    methods: {

      onSubmit () {
        this.loading = true

        createUser(this.form).then(response => {

          this.$message({
            message: 'Compte créer, un email à été envoyé au client',
            type: 'info'
          })
          this.form.email = null
          this.form.name = null
          this.loading = false
          this.fetchUsers()
        }).catch(err => {
          this.$message({
            message: 'Impossible de créer le compte.',
            type: 'warning'
          })
          this.loading = false
        })
      },

      handleAdmin(index, row) {

        roleUser(row.id).then(response => {

          this.$message({
            message: 'Rôle modifié',
            type: 'info'
          })

          this.fetchUsers()

        }).catch(err => {
          this.$message({
            message: 'Impossible de traiter cette demande',
            type: 'warning'
          })
        })

      },

      handleConfirmedDelete() {
        this.dialogVisible = false

        deleteUser(this.handleDeleteId).then(response => {

          this.$message({
            message: 'Utilisateur supprimé',
            type: 'info'
          })

          this.fetchUsers()

        }).catch(err => {
          this.$message({
            message: 'Impossible de traiter cette demande',
            type: 'warning'
          })
        })

        this.handleDeleteId = null
      },

      handleExport(row) {

        this.$router.push({ name: 'Exports', params: { userId: row.id } })
      },

      handleDelete(index, row) {
        this.dialogVisible = true
        this.handleDeleteId = row.id
      },

      fetchUsers() {
        fetchUsers().then(response => {
          this.users = response.data
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
