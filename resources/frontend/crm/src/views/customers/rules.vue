<template>
  <div class="app-container">

    <el-tabs value="first">
      <el-tab-pane label="Règles" name="first">

        <el-select v-model="customer_id" placeholder="Select" style="margin-bottom: 20px; width: 100%" clearable>
          <el-option
            v-for="(item, key) in customers"
            :key="item.id"
            :label="item.name"
            :value="item.id">
          </el-option>
        </el-select>


        <el-table
          v-loading.fullscreen.lock="loading"
          v-if="rules"
          :data="rules"
          element-loading-text="Chargement"
          border
          fit
          highlight-current-row>

          <el-table-column type="expand" label="+">
            <template slot-scope="props">
              <p v-show="props.row.monday">Lundi</p>
              <p v-show="props.row.tuesday">Mardi</p>
              <p v-show="props.row.wednesday">Mercredi</p>
              <p v-show="props.row.thursday">Jeudi</p>
              <p v-show="props.row.friday">Vendredi</p>
              <p v-show="props.row.sunday">Samedi</p>
              <p v-show="props.row.monday">Dimanche</p>
            </template>
          </el-table-column>

          <el-table-column
            prop="name"
            label="Nom de la règle"
            sortable
            column-key="name"
          />

          <el-table-column
            label="Code postaux">
            <template slot-scope="scope">
              {{ showZip(scope.row.zip) }}
            </template>
          </el-table-column>

          <el-table-column
            label="Plage horaire">
            <template slot-scope="scope">
              {{ showHours(scope.row.hours) }}
            </template>
          </el-table-column>


          <el-table-column
            prop="customer_id"
            label="Client"
            sortable
            column-key="customer_id"
          />

          <el-table-column
            prop="leads_number"
            label="Nombre de lead"
            sortable
            column-key="leads_number"
          />

          <el-table-column
            label="Landings">
            <template slot-scope="scope">

              {{ showLandings(scope.row.landings) }}

            </template>
          </el-table-column>



          <el-table-column
            label="Actions">
            <template slot-scope="scope">

              <el-button size="mini" @click="handleRapport(scope.row)">
                Historique des rapports
              </el-button>

              <el-button
                size="mini"
                type="danger"
                @click="handleDelete(scope.$index, scope.row)">Supprimer</el-button>

            </template>
          </el-table-column>

        </el-table>

      </el-tab-pane>

      <el-tab-pane label="Créer une règle" name="second">

        <el-form ref="form" :model="form" label-width="180px">

          <el-form-item label="Nommer cette règle">
            <el-input placeholder="Entrez quelque chose" v-model="form.name"></el-input>
          </el-form-item>

          <el-form-item label="Code postaux (facultatif)">
            <el-select v-model="form.zip" multiple filterable allow-create default-first-option placeholder="Select"></el-select>
          </el-form-item>


          <el-form-item label="Client" style="width: 100%">
            <el-select v-model="form.customer_id" placeholder="Select" style="width: 100%">
              <el-option
                v-for="(item, key) in customers"
                :key="item.id"
                :label="item.name"
                :value="item.id">
              </el-option>
            </el-select>
          </el-form-item>

          <el-form-item label="Landings">
            <el-select v-model="form.landings" multiple placeholder="Select" style="width: 100%">
              <el-option
                v-for="(item, key) in landings"
                :key="item.id"
                :label="item.name"
                :value="item.id">
              </el-option>
            </el-select>
          </el-form-item>

          <el-form-item label="Jours d'exécution">

            <el-checkbox v-model="form.monday">Lundi</el-checkbox>
            <el-checkbox v-model="form.tuesday">Mardi</el-checkbox>
            <el-checkbox v-model="form.wednesday">Mecredi</el-checkbox>
            <el-checkbox v-model="form.thursday">Jeudi</el-checkbox>
            <el-checkbox v-model="form.friday">Vendredi</el-checkbox>
            <el-checkbox v-model="form.saturday">Samedi</el-checkbox>
            <el-checkbox v-model="form.sunday">Dimanche</el-checkbox>


          </el-form-item>

          <el-form-item label="Plage horaire">
            <el-time-picker
              is-range
              v-model="form.hours"
              range-separator="à"
              value-format="HH:mm"
              format="HH:mm"
              start-placeholder="Horaire de début"
              end-placeholder="Horaire de fin">
            </el-time-picker>

          </el-form-item>


          <el-form-item label="Leads max par jour">
            <el-input-number v-model="form.leads_number" :min="1" :max="10000"></el-input-number>

          </el-form-item>

          <el-form-item>
            <el-button type="primary" @click="onSubmit">Créer cette règle</el-button>
          </el-form-item>
        </el-form>

      </el-tab-pane>
    </el-tabs>

    <el-dialog
      title="Confirmation"
      :visible.sync="dialogVisible"
      width="30%">
      <span>Souhaitez-vous vraiment supprimer cette règle ?</span>
      <span slot="footer" class="dialog-footer">
      <el-button @click="dialogVisible = false">Annuler</el-button>
      <el-button type="danger" @click="handleConfirmedDelete">Supprimer</el-button>
    </span>
    </el-dialog>

  </div>
</template>

<script>
  import { fetchAllLandings, fetchCustomers, createRules, fetchRules, deleteRule } from '@/api/article'
  import { mapGetters } from 'vuex'

  export default {
    name: 'Customers',
    data() {
      return {
        customers: null,
        landings: null,
        rules: null,
        loading: true,
        customer_id: null,
        rapportsSearch: null,
        dialogVisible: false,
        handleDeleteId: null,
        form: {
          customer_id: null,
          name: null,
          landings: null,
          monday: null,
          tuesday: null,
          wednesday: null,
          thursday: null,
          friday: null,
          saturday: null,
          sunday: null,
          zip: [],
          hours: ['00:00', '23:59'],
          leads_number: 20
        }
      }
    },
    computed: {
      ...mapGetters([
        'token'
      ])
    },

    mounted() {
      this.customer_id = parseInt(this.$route.params.userId);
      this.form.customer_id = parseInt(this.$route.params.userId);
      this.fetchCustomers()
      this.fetchLandings()
      this.fetchRules()
    },

    watch: {
      customer_id: function (val) {
        this.fetchRules()
        this.form.customer_id = this.customer_id
      }
    },

    methods: {

      onSubmit () {
        this.loading = true

        createRules(this.form).then(response => {

          if (response.data.status) {
            this.$message({
              message: 'Règle ajoutée',
              type: 'success'
            })
            this.fetchRules()
            this.form.name = null
            this.form.landings = []
            this.form.leads_number = 20
            this.form.monday = null
            this.form.tuesday = null
            this.form.wednesday = null
            this.form.thursday = null
            this.form.friday = null
            this.form.saturday = null
            this.form.sunday = null
            this.form.zip = []
            this.form.hours = ['00:00', '23:59']
          } else {
            this.$message({
              message: 'Merci de verifier les champs',
              type: 'danger'
            })
          }
          this.loading = false

        }).catch(err => {
          this.$message({
            message: 'Impossible de créer la règle',
            type: 'warning'
          })
          this.loading = false
        })
      },

      handleDelete(index, row) {
        this.dialogVisible = true
        this.handleDeleteId = row.id
      },

      handleConfirmedDelete() {
        this.dialogVisible = false

        deleteRule(this.handleDeleteId).then(response => {

          this.$message({
            message: 'Règle supprimée',
            type: 'info'
          })

          this.fetchRules()

        }).catch(err => {
          this.$message({
            message: 'Impossible de traiter cette demande',
            type: 'warning'
          })
        })

        this.handleDeleteId = null
      },


      handleRapport(row) {

        this.$router.push({ name: 'Rapports', params: { rule_id: row.id } })
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

      fetchRules() {
        fetchRules(this.customer_id).then(response => {
          this.rules = response.data
          this.loading = false
        }).catch(err => {
          this.$message({
            message: 'Impossible de récupérer les données.',
            type: 'warning'
          })
          this.loading = false
        })
      },

      showZip (data) {
        if (data.length == 0)
          return 'tous';

        var names = '';
        data.forEach(function(element) {
          names = names + element + ', ';
        });
        return names;
      },

      showLandings (data) {
        var names = '';
        data.forEach(function(element) {
          names = names + element.name + ', '
        });

        return names;
      },

      showHours (data) {
        var names = '';
        data.forEach(function(element) {
          if (names.length == 0)
            names = names + element + ' à '
          else
            names = names + element
        });

        return names;
      },

      fetchLandings() {
        fetchAllLandings().then(response => {
          this.landings = response.data
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
