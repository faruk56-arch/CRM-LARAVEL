<template>
  <div class="app-container">
    <h3>
      <span v-for="(item, index) in landings" v-show="selectedLandings.includes(item.id)">
        {{ item.name }}
        <small v-if="index != landings.length - 1">-</small>
      </span>
    </h3>
    <el-tabs type="card" @tab-click="handleClickTab">
      <el-tab-pane label="Nouveaux" name="new">&nbsp;</el-tab-pane>
      <el-tab-pane label="Extraits" name="extracted">&nbsp;</el-tab-pane>
      <el-tab-pane label="Archivés" name="archived">&nbsp;</el-tab-pane>
      <el-tab-pane label="Mauvais" name="trashed">&nbsp;</el-tab-pane>
      <el-tab-pane label="Production" name="production">&nbsp;</el-tab-pane>
      <el-tab-pane label="Convertis" name="convertis">&nbsp;</el-tab-pane>
      <el-tab-pane label="Production extraites" name="production_extracted"/>
      <el-tab-pane label="En Stock" name="stock"/>

      <el-tab-pane label="Landings" name="landings"/>
      <el-tab-pane label="Import CSV/XLSX" name="import"/>
    </el-tabs>

    <div v-show="['new', 'extracted', 'archived', 'trashed', 'doublons'].includes(category)">
      <el-input v-model="filename" placeholder="Nom du fichier" style="width:340px;" prefix-icon="el-icon-document"/>
      <BookTypeOption v-model="bookType" />
      <el-select
        v-model="form_for_customer"
        filterable
        allow-create
        default-first-option
        placeholder="Vendu à">
        <el-option
          v-for="item in for_customer"
          :key="item.for_customer"
          :label="item.for_customer"
          :value="item.for_customer">
        </el-option>
      </el-select>

      <el-button :loading="downloadLoading" style="margin-bottom:20px" type="primary" icon="el-icon-download" @click="handleDownload">Extraire</el-button>
      <br><el-button :loading="downloadLoading" style="margin-bottom:20px" type="secondary" icon="el-icon-news" @click="handleArchiver">Archiver</el-button>
      <el-button :loading="downloadLoading" style="margin-bottom:20px" type="warning" icon="el-icon-delete" @click="handleTrash">Mauvais</el-button>
      <el-button :loading="downloadLoading" style="margin-bottom:20px" type="danger" icon="el-icon-delete" @click="dialogVisible = true">Suppresion definitive</el-button>
      <br>

      <div shadow="never" v-if="exports" style="text-align: center; width: 100%;">
        <a :href="exports.url + '/' + exports.token + '/' + exports.filename" target="_blank">
          <el-button type="primary">Cliquez ici pour télécharger l'export</el-button>
        </a>
        <el-button type="success" v-on:click="copytoclip">Cliquez ici pour copier le lien de l'export</el-button>
      </div>

      <label class="radio-label">Nombre de résultats: </label>
      <el-input v-model="totalLimit" @change="fetchData" type="number" min="0" max="500" placeholder="Total Max" style="width:150px; margin-bottom: 20px;" prefix-icon="el-icon-tickets">
        <!--<el-button type="primary" slot="append" icon="el-icon-check" @click="fetchData"></el-button>-->
      </el-input>

      <el-button type="primary" @click="dialogConvertVisible = true">Convertir les leads sélectionnés</el-button>

      <el-table
        v-loading.fullscreen.lock="listLoading"
        v-if="list"
        ref="multipleTable"
        :data="list"
        element-loading-text="Chargement"
        border
        fit
        highlight-current-row
        @selection-change="handleSelectionChange">
        <el-table-column type="selection" align="center"/>
        <el-table-column :label="'Total leads: ' + list.length + '   |  Total sélectionné(s): ' + multipleSelection.length">
          <el-table-column v-for="i in listCpy" :prop="i" :label="i" :key="i" :render-header="TableHeaderFilter" sortable min-width="120">
            <template slot-scope="scope">
              <span v-if="i != 'rapport_id'">{{ scope.row[i] }}</span>
            </template>

          </el-table-column>
        </el-table-column>
      </el-table>

    </div>

    <div v-show="['production', 'stock', 'convertis', 'production_extracted'].includes(category)">

      <div class="block">
        <span>Résultat entre deux dates</span>
        <el-date-picker
          v-model="listDepDate"
          type="date"
          placeholder="Choisir une date">
        </el-date-picker>

        <el-date-picker
          v-model="listDepDate2"
          type="date"
          placeholder="Chsoir une date">
        </el-date-picker>

        <el-checkbox v-model="zone_climatique" v-if="landingData && landingData.zone_climatique">Affichage par zones climatiques</el-checkbox>
      </div>


      <el-table
        v-loading="listLoading"
        v-if="listDep"
        :data="listDep"
        style="width: 100%"
        :cell-class-name="tableRowStyle"
        :row-class-name="tableRowClassName"
        :highlight-current-row="false"
      >
        <el-table-column
          v-if="!zone_climatique"
          prop="departement"
          sortable
          label="Département">
        </el-table-column>
        <el-table-column
          v-if="zone_climatique"
          prop="zone_climatique"
          sortable
          label="Zone Climatique">
        </el-table-column>
        <el-table-column
          prop="value"
          label="Lead disponible"
          sortable
          column-key="value"
        >
        </el-table-column>

        <el-table-column
          prop="taboola"
          label="Taboola"
          sortable
          column-key="taboola"
        ></el-table-column>

        <el-table-column
          prop="facebook"
          label="Facebook"
          sortable
          column-key="facebook"
        ></el-table-column>

        <el-table-column
          prop="google"
          label="Google"
          sortable
          column-key="google"
        ></el-table-column>

        <el-table-column

          prop="sms"
          label="Sms"
          sortable
          column-key="sms"
        ></el-table-column>

        <el-table-column
          prop="email"
          label="Email"
          sortable
          column-key="email"
        ></el-table-column>

        <el-table-column
          prop="outbrain"
          label="Outbrain"
          sortable
          column-key="outbrain"
        ></el-table-column>

        <el-table-column
          prop="internal"
          label="Internal"
          sortable
          column-key="internal"
        ></el-table-column>


        <el-table-column
          prop="autre"
          label="Autres"
          sortable
          column-key="autre"
        ></el-table-column>
      </el-table>
    </div>


    <div v-show="['landings'].includes(category)">
      <h2>Sélectionner des landings</h2>


      <el-checkbox-group v-model="selectedLandings" :min="1">
        <el-checkbox v-for="item in landings" :key="item.id" :label="item.id">{{item.name}}</el-checkbox>
      </el-checkbox-group>

      <div style="margin-top: 20px;">
      <el-button type="primary" v-on:click="selectedLandingsHandle">Voir les résultats</el-button>
      </div>

    </div>


    <div v-show="['import'].includes(category)">
      <h2>Import CSV/XLSX</h2>

      <el-alert
        v-if="selectedLandings.length > 1"
        title="Import n'est pas disponible si plusieurs landings sont sélectionnées"
        type="warning">
      </el-alert>


      <div v-if="selectedLandings.length <= 1">

        <el-alert
          v-if="!columns.length"
          title="Chargement des colonnes"
          type="info">
        </el-alert>

        <div style="margin-bottom: 20px">
          1. Sélection de la source* <small>(permet de définir le champ : utm_medium) </small>:


          <el-select
            v-model="form_import_form"
            filterable
            allow-create
            default-first-option
            placeholder="Choisissez la source d'acquisition">
            <el-option
              v-for="item in import_from"
              :key="item.import_from"
              :label="(item.import_from == 'facbook') ? 'facebook' : item.import_from"
              :value="item.import_from">
            </el-option>
          </el-select>
          <p><small>Vous pouvez créer une nouvelle source en cliquant dessus</small></p>
        </div>


        <div v-show="form_import_form">
          <p>2. Import du fichier et choix des colonnes :</p>

          <p>Colonnes requises :</p>
          <ul>
            <li v-for="item in columns">{{item.value}}</li>
          </ul>

          <xls-csv-parser v-if="columns.length" :columns="columns" @on-validate="onValidate" :help="help" lang="fr"></xls-csv-parser>
        </div>

      </div>

    </div>
    <el-dialog
      title="Suppression définitive"
      :visible.sync="dialogVisible"
      width="30%">
      <span>Souhaitez-vous vraiment supprimer ces leads définitivements ?</span>
      <span slot="footer" class="dialog-footer">
    <el-button type="" @click="dialogVisible = false">Annuler</el-button>
    <el-button type="danger" @click="handleDelete">Confirmer</el-button>
  </span>
    </el-dialog>



    <el-dialog title="Convertir des leads pour une autre Landing" :visible.sync="dialogConvertVisible">
      <el-form>
        <el-form-item label="À convertir sur">
          <el-select v-model="convertForm.landing" placeholder="Sélectionnez une zone">
            <el-option v-for="item in landings" :label="item.name" :key="item.id" :value="item.id"></el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
    <el-button type="primary" @click="convertLead">Confirmer</el-button>
    <el-button @click="dialogConvertVisible = false">Annuler</el-button>
  </span>
    </el-dialog>


  </div>
</template>

<script>
import { fetchList, putList, fetchListDep, postExport, postImport, fetchLanding, deleteList, fetchAllLandings, convertLead } from '@/api/article'
import BookTypeOption from './components/BookTypeOption'
import { mapGetters } from 'vuex'
import { XlsCsvParser } from 'vue-xls-csv-parser';

export default {
  name: 'SelectExcel',
  components: { BookTypeOption, XlsCsvParser },
  props: {
    landingName: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      category: 'new',
      totalLimit: 150,
      search: null,
      form_for_customer: null,
      form_import_form: null,
      form_source_lists: [],
      dialogConvertVisible: false,
      dialogVisible: false,
      list: null,
      listCpy: false,
      listDep: null,
      listDepDate: new Date(),
      listDepDate2: new Date(),
      landings: null,
      searchChange: 1,
      ids: null,
      listLoading: true,
      multipleSelection: [],
      downloadLoading: false,
      filename: '',
      bookType: 'xlsx',
      timeout: null,
      landingId: null,
      exports: null,
      landingData: null,
      for_customer:[],
      import_from: [],
      zone_climatique: false,
      selectedLandings: [],
      convertForm: {
        landing: null
      },
      columns: [],
      results: null,
      help: '',
    }
  },
  computed: {
    ...mapGetters([
      'token'
    ])
  },
  watch: {
    listDepDate: function(val) {

      if (this.category == 'production' || this.category == 'stock' || this.category == 'convertis' || this.category == 'production_extracted') {
        this.fetchDataDep()
      }

    },
    zone_climatique: function(val) {
      this.fetchDataDep()
    },
    listDepDate2: function(val) {

      if (this.category == 'production' || this.category == 'stock' || this.category == 'convertis' || this.category == 'production_extracted') {
        this.fetchDataDep()
      }

    },
    searchChange: function(val) {
      return this.applyFilters()
    },

    '$route.params.id': function(id) {
      this.fetchDataLanding()
      this.fetchData()
    },
  },

  created() {
    if (this.$route.params.id == '0')
      return this.$router.push('/');
    else {
      this.listCpy = false;
      this.fetchLandings()
      this.fetchDataLanding()
      this.fetchData()
      this.ids = this.$route.params.id;
      this.selectedLandings = this.ids.split(",").map(Number);
    }
  },

  methods: {

    selectedLandingsHandle() {
      this.$router.push({ name: 'Leadview', params: { id: this.selectedLandings.toString() } })
    },

    onValidate(results) {
      this.results = results;
      this.handleImport();
    },

    formatDate(d) {
      var month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

      if (month.length < 2) month = '0' + month;
      if (day.length < 2) day = '0' + day;

      return [year, month, day].join('-');
    },

    applyFilters() {
      this.list = this.listCpy
      this.list = this.list.filter(row => {
        let ret;
        for (const filter in this.search) {
          ret = row[filter].toLowerCase().includes(this.search[filter].toLowerCase())
          if (!ret) { return ret }
        }
        return true
      })
    },
    handleClickTab(tab, event) {
      this.category = tab.name

      if (['production', 'stock', 'convertis', 'production_extracted'].includes(tab.name)) {
        this.fetchDataDep()
      } else if (['landings'].includes(tab.name)) {
      } else {
        this.fetchData()
      }
    },
    TableHeaderFilter(h, { column, $index }) {
      const self = this
      if ($index != 1) {
        return h('span', { class: 'header' }, [h('span', { class: 'header-label' }, column.label), h('el-input', {
          props: { size: 'mini' },
          style: 'font-weight: debouncedebnormal; padding: 0',
          on: {
            input(e) {
              //            console.log(e)
              if (!self.search) { self.search = {} }
              self.search[column.label] = e


              clearTimeout(self.timeout)
              self.timeout = setTimeout(() => {
                self.fetchData()
              }, 400)
            }
          },
          nativeOn: {
            click(e) {
              e.stopPropagation()
            }
          }
        })])

      } else {
        return h('span', {class: 'header'}, [h('span', {class: 'header-label'}, column.label), h('el-date-picker', {
          props: {size: 'small', type: 'daterange'},
          style: 'font-weight: debouncedebnormal; padding: 0',
          on: {
            input(e) {
              if (!self.search) {
                self.search = {}
              }
              self.search[column.label] = self.formatDate(e[0]) + '<>' + self.formatDate(e[1])


              clearTimeout(self.timeout)
              self.timeout = setTimeout(() => {
                self.fetchData()
              }, 400)
            }
          },
          nativeOn: {
            click(e) {
              e.stopPropagation()
            }
          }
        })])
      }

    },
    tableFilter(data) {
      for (const el in data) {
        if (this.search[el] && this.search[el] !== data[el]) { return 0 }
      }
      return (1)
    },
    fetchDataLanding() {
      fetchLanding(this.$route.params.id).then(response => {
        this.landingData = response.data
        this.zone_climatique = false
      }).catch(err => {
        this.$message({
          message: 'Impossible de récupérer les données de la LP.',
          type: 'warning'
        })
      })
    },
    fetchLandings() {
      fetchAllLandings().then(response => {
        this.landings = response.data
      }).catch(err => {
        this.$message({
          message: 'Une erreur est survenue',
          type: 'warning'
        })
      })
    },

    fetchDataDep() {
      this.listLoading = true
      fetchListDep(this.listQuery, this.$route.params.id, this.category, this.formatDate(this.listDepDate), this.formatDate(this.listDepDate2), this.zone_climatique).then(response => {

        this.listDep = response.data
        this.listLoading = false
      }).catch(err => {
        this.listLoading = false
        if (err.response.data.message && err.response && err.response.data && err.response.data.message) {
          this.$message({
            message: 'Aucun lead',
            type: 'warning'
          })
        } else {
          this.$message({
            message: 'Impossible de récupérer les leads',
            type: 'warning'
          })
        }
      })
    },

  fetchData() {
      this.listLoading = true
      fetchList(this.listQuery, this.$route.params.id, `?_limit=${this.totalLimit}&entry_status=${this.category}${this.filterAsString()}`).then(response => {

        this.list = response.data.data
        if (this.listCpy === false)
          this.listCpy = response.data.keys

        this.columns = [];
        _.without(response.data.keys, 'id', 'created_at', "updated_at", "utm_source", "utm_medium", "utm_campaign", 'source', 'url_presale', 'visitor', 'convert', 'landing').forEach(element => {
            this.columns.push({ name: element, value: element, isOptional: true })
          });

        this.for_customer = response.data.for_customer;
        this.import_from = response.data.import_from;


        this.listLoading = false


      }).catch(err => {
        this.listLoading = false
        if (err.response.data.message && err.response && err.response.data && err.response.data.message) {
          this.$message({
            message: 'Aucun lead',
            type: 'warning'
          })
        } else {
          this.$message({
            message: 'Impossible de récupérer les leads',
            type: 'warning'
          })
        }
      })
    },

    convertLead() {
      this.listLoading = true;
      this.dialogConvertVisible = false;

      const elems = []
      for (const s of this.multipleSelection) {
        elems.push(s.id)
      }
      const params = {
        landing: this.convertForm.landing,
        leads: elems
      }

      if (!elems.length || elems.length == 0) {
        this.listLoading = false;
        return this.$message({
          message: 'Veuillez sélectionner au moins un lead à convertir',
          type: 'warning'
        });
      }

      convertLead(params).then(response => {
        this.$message({
          message: 'Leads convertis',
          type: 'info'
        })

        this.fetchData()

      }).catch(err => {
        this.$message({
          message: 'Impossible de traiter cette demande',
          type: 'warning'
        })
      })


    },

    putData(newStatus) {
      const elems = []
      for (const s of this.multipleSelection) {
        elems.push(s.id)
      }
      const params = {
        entry_status: newStatus,
        leads: elems
      }

      putList(params).then(response => {
        const message = {
          'archived': 'Leads archivés',
          'trashed': 'Leads placés à la corbeille',
          'new': 'Classé comme nouveau',
          'extracted': 'Leads extraits'
        }

        this.$message({
          message: message[newStatus],
          type: 'info'
        })

        this.fetchData()
      }).catch(err => {
        this.$message({
          message: 'Impossible de traiter cette demande',
          type: 'warning'
        })
      })
      // }
    },
    filterAsString() {
      let ret = ''
      for (const s in this.search) {
        if (this.search[s]) {
          ret += `&${s}=${this.search[s]}`
        }
      }
      return ret
    },
    handleSelectionChange(val) {
      this.multipleSelection = val
    },
    handleArchiver() {
      this.putData('archived')
    },
    handleTrash() {
      this.putData('trashed')
    },

    handleDelete() {
      this.dialogVisible = false
      const elems = []
      for (const s of this.multipleSelection) {
        elems.push(s.id)
      }
      const params = {
        leads: elems
      }

      deleteList(params).then(response => {
        this.$message({
          message: 'Leads supprimés',
          type: 'info'
        })
        this.fetchData()
      }).catch(err => {
        this.$message({
          message: 'Impossible de traiter cette demande',
          type: 'warning'
        })
      })
    },
    handleDownload() {
      this.exports = null
      if (this.multipleSelection && this.multipleSelection.length) {

        var arr = [];

        this.multipleSelection.forEach(function(element) {
          arr.push(element.id)
        });

        const params = {
          bookType: this.bookType,
          filename: this.filename,
          leads: arr,
          for_customer: this.form_for_customer
        }


        postExport(params, this.$route.params.id.split(',')[0]).then(response => {


          this.exports = response.data

          this.$message({
            message: 'Export disponible',
            type: 'success'
          })

          this.putData('extracted')

          // this.fetchData()
        }).catch(err => {
          this.$message({
            message: 'Impossible de traiter cette demande',
            type: 'warning'
          })
        })



      } else {
        this.$message({
          message: 'Merci de sélectionner des leads',
          type: 'warning'
        })
      }

    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]))
    },

    tableRowStyle({row, column, rowIndex, columnIndex}) {
      if (columnIndex) {
        return 'strong'
      }
    },

    tableRowClassName({row, rowIndex}) {
      if (row.value >= 100) {
        return 'success-row';
      } else if (row.value >= 50) {
        return 'warning-row';
      }
      return '';
    },

    copyStringToClipboard (str) {
      // Create new element
      var el = document.createElement('textarea');
      // Set value (string to be copied)
      el.value = str;
      // Set non-editable to avoid focus and move outside of view
      el.setAttribute('readonly', '');
      el.style = {position: 'absolute', left: '-9999px'};
      document.body.appendChild(el);
      // Select text inside element
      el.select();
      // Copy text to clipboard
      document.execCommand('copy');
      // Remove temporary element
      document.body.removeChild(el);
    },


    handleImport() {

        const params = {
          data: this.results,
          columns: this.columns,
          form_import_from: this.form_import_form,
        };

        console.log(params, this.$route.params.id);


        postImport(params, this.$route.params.id).then(response => {
          this.exports = response.data;

          this.fetchData();

          this.$message({
            message: 'Leads importées',
            type: 'success'
          });


        }).catch(err => {
          this.$message({
            message: 'Impossible d\'importer votre fichier, veuillez le vérifier.',
            type: 'warning'
          })
        })
    },

    copytoclip() {
      const cc = this.exports.url + '/' + this.exports.token + '/' + this.exports.filename;
      this.copyStringToClipboard(cc)

      this.$message({
        message: 'Copié',
        type: 'info'
      })
    }
  }
}
</script>
