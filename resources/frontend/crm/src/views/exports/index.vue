<template>
  <div class="app-container">

    <el-form ref="form" :model="form" label-width="120px" style="margin-bottom: 20px">
      Landing :
      <el-select v-model="form.landing_id" multiple placeholder="Select" style="margin-right: 10px" clearable>
        <el-option
          v-for="(item, key) in landings"
          :key="key"
          :label="item"
          :value="key">
        </el-option>
      </el-select>

      User :
      <el-select v-model="form.user_id" placeholder="Select" style="margin-right: 10px" clearable>
        <el-option
          v-for="(item, key) in users"
          :key="key"
          :label="item"
          :value="key">
        </el-option>
      </el-select>

      <br><br>
      Date de création :
      <el-date-picker
        style="margin-right: 20px"
        v-model="form.created_at"
        type="daterange"
        range-separator="à"
        start-placeholder="Date de début"
        end-placeholder="Date de fin">
      </el-date-picker>

      Vendu à :
      <el-select v-model="form.for_customer" autocomplete placeholder="Select" style="margin-right: 10px" clearable>
        <el-option
          v-for="item in for_customer"
          :key="item.for_customer"
          :label="item.for_customer"
          :value="item.for_customer">
        </el-option>
      </el-select>

    </el-form>

    <br><br>

    <el-collapse v-model="activeNames">
      <el-collapse-item title="Générer un export personnalisé" name="1" style="background-color: #f2f2f2">
        <div v-if="landings">
          Landings :
          <el-select v-model="form_export.landings" multiple placeholder="Select" style="margin-right: 20px; width: 100%;" clearable>
            <el-option
              v-for="(item, key) in landings"
              :key="key"
              :label="item"
              :value="key">
            </el-option>
          </el-select>
        </div>
        <p style="visibility: hidden; height: 1px; font-size: 1px"> {{ landings}}</p>
        <br>

        <div>
          Statuts :
          <el-select v-model="form_export.status" multiple style="margin-right: 20px; width: 100%;" clearable>
            <el-option
              v-for="item in options"
              :key="item.value"
              :label="item.label"
              :value="item.value">
            </el-option>
          </el-select>
        </div>
        <br>

        <div>
          Plage de date :
          <el-date-picker
            v-model="form_export_date"
            type="daterange"
            range-separator="à"
            start-placeholder="Date de début"
            end-placeholder="Date de fin">
          </el-date-picker>
        </div>
        <br>
        <el-button type="primary" :loading="loading_button" @click="handleForm">Générer</el-button>

      </el-collapse-item>

    </el-collapse>

    <div>
      <h4>Total lead sélection : <small>{{total_leads}}</small></h4>

    </div>

        <el-table
          v-loading.fullscreen.lock="loading"
          v-if="exports"
          :data="exports"
          element-loading-text="Chargement"
          border
          fit
          highlight-current-row>

          <el-table-column
            prop="landing"
            label="Landing"
            sortable
            column-key="landing"
          />

          <el-table-column
            prop="user"
            label="User"
            sortable
            column-key="user"
          />

          <el-table-column
            prop="filename"
            label="File"
            sortable
            column-key="filename"
          />

          <el-table-column
            prop="count"
            label="Total leads"
            sortable
            column-key="count"
          />

          <el-table-column
            prop="created"
            label="Created"
            sortable
            column-key="created"
          />

          <el-table-column
            prop="for_customer"
            label="Vendu à"
            sortable
            column-key="for_customer"
          />

          <el-table-column
            label="Actions">
            <template slot-scope="scope">
              <a target="_blank" :href="'/api/exports/' + exports[scope.$index].token + '/' + exports[scope.$index].filename"><el-button
                size="mini"
                type="success">Télécharger</el-button></a>

              <el-button v-if="!exports[scope.$index].big_export" type="danger" size="mini" @click="handleInsert(scope.row)">Ré-injecter</el-button>

            </template>
          </el-table-column>

        </el-table>

  </div>
</template>

<script>
  import { getExports, exportInsert, generateBigImport } from '@/api/article'
  import { mapGetters } from 'vuex'

  export default {
    name: 'Users',
    data() {
      return {
        outerVisible: false,
        exports: null,
        total_leads: 0,
        loading: true,
        loading_button: false,
        landings: [],
        users: null,
        options: [
            {value: "new", label: "Nouveaux"},
            {value: "extracted", label: "Extraits"},
            {value: "archived", label: "Archivés"},
            {value: "converted", label: "Convertis"},
            {value: "trashed", label: "Mauvais"}
          ],
        activeNames: [],
        for_customer: [],

        form_export_date: null,
        form_export: {
          landings: null,
          status: '',
          start: null,
          end: null,
        },

        form: {
          for_customer: null,
          landing_id: null,
          user_id: null,
          created_at: null
        }
      }
    },
    computed: {
      ...mapGetters([
        'token'
      ])
    },

    mounted() {
      this.form.user_id = this.$route.params.userId
      this.fetchExports()
    },

    watch: {
      form: {
        handler: function(val, oldVal) {
          if (this.form.created_at && Array.isArray(this.form.created_at)) {
            this.form.created_at = this.formatDate(this.form.created_at[0]) + '<>' + this.formatDate(this.form.created_at[1])
          }

          this.fetchExports()
        },
        deep: true
      },
      form_export_date: function() {
        if (this.form_export_date && Array.isArray(this.form_export_date)) {
          this.form_export.start = this.formatDate(this.form_export_date[0])
          this.form_export.end = this.formatDate(this.form_export_date[1])
        }
      }
    },


    methods: {

      formatDate(d) {
        var month = '' + (d.getMonth() + 1),
          day = '' + d.getDate(),
          year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
      },

      handleInsert(row) {
        this.$confirm('Souhaitez-vous vraimet ré-injecter les leads ?', 'Warning', {
          confirmButtonText: 'Ré-insérer les leads',
          cancelButtonText: 'Annuler',
          type: 'danger'
        }).then(() => {

          exportInsert(row.id).then(response => {
            console.log(response)
            this.$message({
              type: 'info',
              message: 'Leads inséres'
            });
          }).catch(err => {
            this.$message({
              message: 'Une erreur est survenue',
              type: 'warning'
            })
            this.loading = false
          })

        }).catch(() => {
          this.$message({
            type: 'info',
            message: 'Action annulée'
          });
        });
      },

      handleForm() {
        this.loading_button = true

        generateBigImport(this.form_export).then(response => {
          this.loading_button = false
          this.activeNames = []

          this.$message({
            message: 'Si les critères rentrées permettent de trouver des leads, alors un export général sera prochainement disponible sur cette interface (environ 5 à 15 minutes).',
            type: 'info'
          })
        }).catch(err => {
          this.$message({
            message: 'Impossible de générer l\'export.',
            type: 'warning'
          })
          this.loading_button = false
        })
      },

      modalHandle() {
        this.outerVisible = true
      },
      fetchExports() {
        this.loading = true
        getExports(this.form).then(response => {
          this.exports = response.data.data
          this.landings = response.data.landings
          this.total_leads = response.data.total_leads
          this.users = response.data.users
          this.loading = false
          this.for_customer = response.data.for_customer
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
