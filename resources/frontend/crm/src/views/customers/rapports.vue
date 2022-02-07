<template>
  <div class="app-container">

    <div shadow="never" v-if="exports" style="text-align: center; width: 100%;">
      <a :href="exports.url + '/' + exports.token + '/' + exports.filename" target="_blank">
        <el-button type="primary">Cliquez ici pour télécharger l'export</el-button>
      </a>
    </div>

        <el-table
          v-if="rapports"
          :data="rapports"
          style="width: 100%"
          :row-class-name="tableRowClassName"
        >
          <el-table-column
            label="Rapport du"
            sortable
            prop="date">
          </el-table-column>
          <el-table-column
            label="Total leads à envoyer"
            sortable
            prop="leads_number">
          </el-table-column>

          <el-table-column
            label="Leads envoyés"
            sortable
            prop="count">
          </el-table-column>

          <el-table-column
            label="Code postaux">
            <template slot-scope="scope">
              {{ showZip(scope.row.zip) }}
            </template>
          </el-table-column>

          <el-table-column
            label="Actions">
            <template slot-scope="scope">


              <el-button
                size="mini"
                type="success"
                @click="handleRequestDownload(scope.$index, scope.row)">Télécharger le rapport</el-button>

            </template>
          </el-table-column>

        </el-table>


    <el-dialog
      title="Téléchargement du rapport"
      :visible.sync="dialogVisible"
      width="30%">
      <span>Souhaitez-vous télecharger le rapport ?</span>

      <br><br>

      <el-select v-model="form.bookType" style="width:100%" >
        <el-option
          v-for="item in options"
          :key="item"
          :label="item"
          :value="item"/>
      </el-select>

      <span slot="footer" class="dialog-footer">
      <el-button @click="dialogVisible = false">Annuler</el-button>
      <el-button type="success" @click="handleDownload">Télécharger</el-button>
    </span>
    </el-dialog>


  </div>
</template>

<script>
  import { fetchRapports, postExport } from '@/api/article'
  import { mapGetters } from 'vuex'

  export default {
    name: 'Customers',
    data() {
      return {
        loading: true,
        dialogVisible: false,
        handleDownloadData: null,
        options: ['xlsx', 'csv', 'pdf', 'txt'],
        rapports: null,
        exports: null,
        form: {
          bookType: 'pdf',
          filename: null,
          leads: null,
          rapport: true
        }
      }
    },
    computed: {
      ...mapGetters([
        'token'
      ])
    },

    mounted() {

      this.fetchRapports()
    },


    methods: {

      tableRowClassName({row, rowIndex}) {
        if (row.count >= row.leads_number) {
          return 'success-row';
        }
        if (row.count >= (row.leads_number / 2)) {
          return 'warning-row';
        }
      },

      handleRequestDownload(index, row) {
        this.dialogVisible = true
        this.handleDownloadData = row
      },

      handleDownload () {
        this.dialogVisible = false;
        if (this.handleDownloadData) {
          this.form.rapport = this.handleDownloadData.id;
          this.form.leads = this.handleDownloadData.leads;
          this.form.filename = 'Rapports du ' + this.handleDownloadData.date;

          postExport(this.form, 1).then(response => {

            this.exports = response.data;

            this.$message({
              message: 'Export disponible',
              type: 'success'
            })


          }).catch(err => {
            this.$message({
              message: 'Impossible de traiter cette demande',
              type: 'warning'
            })
          })

          this.handleDownloadData = null;
        }

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

      fetchRapports() {
        fetchRapports(this.$route.params.rule_id).then(response => {
          this.rapports = response.data
          this.loading = false
        }).catch(err => {
          this.$message({
            message: 'Impossible de récupérer les données.',
            type: 'warning'
          })
          this.loading = false
        })
      }

    }
  }
</script>
