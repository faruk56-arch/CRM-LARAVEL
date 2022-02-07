<template>
  <div class="app-container">
    <div class="row">
      <div class="col-md-12">
        <el-date-picker
          v-model="form.date_range"
          type="daterange"
          range-separator="à"
          style="width: 30%"
          start-placeholder="Date de début"
          end-placeholder="Date de fin" v-on:change="onChange()">
        </el-date-picker>

        <el-select v-model="form.landings" v-on:change="onChange()" multiple placeholder="Select" style="width: 65%">
          <el-option
            v-for="item in landings"
            :key="item.id"
            :label="item.name"
            :value="item.id">
          </el-option>
        </el-select>
      </div>

      <div class="col-md-12">
          <div id="container-map-selector" style="height: 600px; width: 50%; position: relative; float: left; margin-top:2rem;"></div>
          <div style="width: 40%; position: relative; float: right; margin-top:2rem;" v-loading="loading">
            <h2>Production</h2>

            <h5> Départements concernés :
              <span v-for="(item, index) in form.departement">
                {{ item.substring(3, 6) }}<span v-if="index != form.departement.length - 1">, </span>
              </span>
            </h5>

            <el-table v-if="result"
              :data="result.sources"
              style="width: 100%">
              <el-table-column
                prop="key"
                label=" ">
              </el-table-column>
              <el-table-column
                prop="value"
                label=" ">
              </el-table-column>
            </el-table>


            <br><br>

            <el-collapse>
              <el-collapse-item title="Statistiques" name="1">

                <el-table v-if="result"
                          :data="result.stats"
                          style="width: 100%">
                  <el-table-column
                    prop="key"
                    label=" ">
                  </el-table-column>
                  <el-table-column
                    prop="value"
                    label=" ">
                  </el-table-column>

                </el-table>

              </el-collapse-item>
            </el-collapse>

          </div>
          <select style="display: none;" id="map-selector" class="form-control" name="departements" v-model="form.departement" multiple="multiple">
            <option value="FR-01">Ain</option>
            <option value="FR-02">Aisne</option>
            <option value="FR-03">Allier</option>
            <option value="FR-04">Alpes-de-Haute-Provence</option>
            <option value="FR-05">Hautes-Alpes</option>
            <option value="FR-06">Alpes-Maritimes</option>
            <option value="FR-07">Ardèche</option>
            <option value="FR-08">Ardennes</option>
            <option value="FR-09">Ariège</option>
            <option value="FR-10">Aube</option>
            <option value="FR-11">Aude</option>
            <option value="FR-12">Aveyron</option>
            <option value="FR-13">Bouches-du-Rhône</option>
            <option value="FR-14">Calvados</option>
            <option value="FR-15">Cantal</option>
            <option value="FR-16">Charente</option>
            <option value="FR-17">Charente-Maritime</option>
            <option value="FR-18">Cher</option>
            <option value="FR-19">Corrèze</option>
            <option value="FR-2A">Corse-du-sud</option>
            <option value="FR-2B">Haute-corse</option>
            <option value="FR-21">Côte-d'or</option>
            <option value="FR-22">Côtes-d'armor</option>
            <option value="FR-23">Creuse</option>
            <option value="FR-24">Dordogne</option>
            <option value="FR-25">Doubs</option>
            <option value="FR-26">Drôme</option>
            <option value="FR-27">Eure</option>
            <option value="FR-28">Eure-et-Loir</option>
            <option value="FR-29">Finistère</option>
            <option selected value="FR-30">Gard</option>
            <option value="FR-31">Haute-Garonne</option>
            <option value="FR-32">Gers</option>
            <option value="FR-33">Gironde</option>
            <option selected value="FR-34">Hérault</option>
            <option value="FR-35">Ile-et-Vilaine</option>
            <option value="FR-36">Indre</option>
            <option value="FR-37">Indre-et-Loire</option>
            <option value="FR-38">Isère</option>
            <option value="FR-39">Jura</option>
            <option value="FR-40">Landes</option>
            <option value="FR-41">Loir-et-Cher</option>
            <option value="FR-42">Loire</option>
            <option value="FR-43">Haute-Loire</option>
            <option value="FR-44">Loire-Atlantique</option>
            <option value="FR-45">Loiret</option>
            <option value="FR-46">Lot</option>
            <option value="FR-47">Lot-et-Garonne</option>
            <option value="FR-48">Lozère</option>
            <option value="FR-49">Maine-et-Loire</option>
            <option value="FR-50">Manche</option>
            <option value="FR-51">Marne</option>
            <option value="FR-52">Haute-Marne</option>
            <option value="FR-53">Mayenne</option>
            <option value="FR-54">Meurthe-et-Moselle</option>
            <option value="FR-55">Meuse</option>
            <option value="FR-56">Morbihan</option>
            <option value="FR-57">Moselle</option>
            <option value="FR-58">Nièvre</option>
            <option value="FR-59">Nord</option>
            <option value="FR-60">Oise</option>
            <option value="FR-61">Orne</option>
            <option value="FR-62">Pas-de-Calais</option>
            <option value="FR-63">Puy-de-Dôme</option>
            <option value="FR-64">Pyrénées-Atlantiques</option>
            <option value="FR-65">Hautes-Pyrénées</option>
            <option value="FR-66">Pyrénées-Orientales</option>
            <option value="FR-67">Bas-Rhin</option>
            <option value="FR-68">Haut-Rhin</option>
            <option value="FR-69">Rhône</option>
            <option value="FR-70">Haute-Saône</option>
            <option value="FR-71">Saône-et-Loire</option>
            <option value="FR-72">Sarthe</option>
            <option value="FR-73">Savoie</option>
            <option value="FR-74">Haute-Savoie</option>
            <option value="FR-75">Paris</option>
            <option value="FR-76">Seine-Maritime</option>
            <option value="FR-77">Seine-et-Marne</option>
            <option value="FR-78">Yvelines</option>
            <option value="FR-79">Deux-Sèvres</option>
            <option value="FR-80">Somme</option>
            <option value="FR-81">Tarn</option>
            <option value="FR-82">Tarn-et-Garonne</option>
            <option value="FR-83">Var</option>
            <option value="FR-84">Vaucluse</option>
            <option value="FR-85">Vendée</option>
            <option value="FR-86">Vienne</option>
            <option value="FR-87">Haute-Vienne</option>
            <option value="FR-88">Vosges</option>
            <option value="FR-89">Yonne</option>
            <option value="FR-90">Territoire de Belfort</option>
            <option value="FR-91">Essonne</option>
            <option value="FR-92">Hauts-de-Seine</option>
            <option value="FR-93">Seine-Saint-Denis</option>
            <option value="FR-94">Val-de-Marne</option>
            <option value="FR-95">Val-d'oise</option>
            <option value="FR-976">Mayotte</option>
            <option value="FR-971">Guadeloupe</option>
            <option value="FR-973">Guyane</option>
            <option value="FR-972">Martinique</option>
            <option value="FR-974">Réunion</option>
          </select>

      </div>

    </div>

    <el-dialog
      title="Tips"
      :visible.sync="dialogVisible"
      width="30%"
      :before-close="handleCloseDialog">
      <span>

      <el-input type="textarea"  show-close="false" v-if="dialogVisible" v-model="regions[getRegion(dialogVisible)].text"></el-input>
      </span>
      <span slot="footer" class="dialog-footer">
    <el-button type="primary" @click="handleCloseDialog">Enregistrer</el-button>
  </span>
    </el-dialog>


  </div>
</template>

<script>

  import { fetchList, fetchAllLandings, fetchProductionsRegion, fetchGetProductionsRegion, postColor, postTextNote } from '@/api/article';
  import { mapGetters } from 'vuex';
  window.jQuery = require('jquery');
  var $ = window.jQuery;
  require('jvectormap');
  require('./mapselector/jquery-jvectormap-fr-merc');

  export default {
    name: 'Customers',
    data() {
      return {
        activeNames: [],
        dialogVisible: false,
        customers: null,
        loading: true,
        landings: [],
        regions: [],
        regions_color: [],
        mapObject: null,
        color_mode: null,
        color: null,
        result : null,
        initiated: false,
        key_pressed: false,
        map: null,
        form: {
          date_range: [new Date(), new Date()],
          landings: null,
          departement: []
        }
      }
    },
    computed: {
      ...mapGetters([
        'token'
      ])
    },

    mounted() {
      this.fetchLandings();
      this.fetchProductions();

      document.addEventListener("keydown", (event) => {
        if (event.keyCode == 16) {
          this.key_pressed = true;
        }
      });

      document.addEventListener("keyup", (event) => {
        if (event.keyCode == 16) {
          this.key_pressed = false;
        }
      });
    },

    watch: {
      'key_pressed': function () {
        console.log(this.key_pressed)
      }
    },

    methods: {

      getRegion(code) {
        return this.regions.findIndex(word => word.region == code);
      },

      handleExport(row) {

        this.$router.push({ name: 'Rules', params: { userId: row.id } })
      },

      colorChange () {
        this.form.departement.forEach((el) => {
          this.mapObject.regions[el].element.config.style.current.fill = this.color;
          this.mapObject.regions[el].element.config.style.initial.fill = this.color;

          this.$message({
            message: 'Couleur attribuée',
            type: 'info'
          })
        });

      },


      submitColor(form) {
        postColor(this.$route.params.category, form).then(response => {
          console.log(response);
        });
      },


      handleCloseDialog() {

        postTextNote(this.$route.params.category, this.dialogVisible, {text: this.regions[this.getRegion(this.dialogVisible)].text}).then(response => {
          this.$message({
            message: 'Notes enregistrées',
            type: 'info'
          })
        });
        this.dialogVisible = false
      },

      formatDate(d) {
        var month = '' + (d.getMonth() + 1),
          day = '' + d.getDate(),
          year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
      },

      fetchProductions() {
        this.loading = true;
        fetchProductionsRegion(this.$route.params.category).then(response => {
          this.regions = response.data.regions;

          $(document).ready(() => {

            this.map = $("#container-map-selector").vectorMap({
              map: 'fr_merc',
              regionsSelectable: true,

              onRegionSelected: (e, code) => {
                if (!this.key_pressed) {
                  this.form.departement = this.mapObject.getSelectedRegions();
                  this.fetchGetProductionsRegion();
                  if (this.initiated)
                    this.submitColor({departement: this.mapObject.getSelectedRegions()});
                } else {
                  this.form.departement.push(code);
                  this.dialogVisible = code;
                  return false;
                }
              },
              regionLabelStyle: {
                initial: {
                  fill: '#B90E32'
                },
                hover: {
                  fill: 'black'
                }
              },
              labels: {
                regions: {
                  render: function (code) {
                    var doNotShow = ['FR-92', 'FR-93', 'FR-94', 'FR-75'];

                    if (doNotShow.indexOf(code) === -1) {
                      return code.split('-')[1];
                    }
                  },
                  offsets: function(code){
                    return {
                      'GP': [-10, 0],
                      '29': [20, 0],
                      '22': [5, 10],
                      '56': [0, -5],
                      '95': [0, -5],
                      '13': [0, -10],
                      '2A': [10, 5],
                      '31': [10, -20],
                      '54': [0, 25],
                      '59': [20, 25]
                    }[code.split('-')[1]];
                  }
                }
              }
              // series: {
              //   regions: [{
              //     values: this.regions_color
              //   }]
              // }
            });
            this.mapObject = $('#container-map-selector').vectorMap('get', 'mapObject');

            this.mapObject.setSelectedRegions(Object.values(response.data.selected));

            setTimeout(() =>  {
              this.initiated = true;
              this.loading = false;
            }, 1500);
          });

        });
      },

      fetchGetProductionsRegion() {
        this.loading = true;
        if (!this.form.landings || !this.form.landings.length)
          return;
        fetchGetProductionsRegion(this.$route.params.category, {date_range: [this.formatDate(this.form.date_range[0]), this.formatDate(this.form.date_range[1])],
          landings: this.form.landings,
          departement: this.form.departement}).then(response => {
          this.result = response.data;
          this.loading = false;
        }).catch(response => {
          this.loading = false;
          this.$message({
            message: 'Aucun lead, veuillez vérifier que vous avez sélectionné une landing. ',
            type: 'warning'
          })
        });
      },

      onChange() {
        this.fetchGetProductionsRegion();
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
      }

    }
  }
</script>

<style>
  svg {
    touch-action: none;
  }

  .jvectormap-container {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
    touch-action: none;
  }

  .jvectormap-tip {
    position: absolute;
    display: none;
    border: solid 1px #CDCDCD;
    border-radius: 3px;
    background: #292929;
    color: white;
    font-family: sans-serif, Verdana;
    font-size: smaller;
    padding: 3px;
  }

  .jvectormap-zoomin, .jvectormap-zoomout, .jvectormap-goback {
    position: absolute;
    left: 10px;
    border-radius: 3px;
    background: #292929;
    padding: 3px;
    color: white;
    cursor: pointer;
    line-height: 10px;
    text-align: center;
    box-sizing: content-box;
  }

  .jvectormap-zoomin, .jvectormap-zoomout {
    width: 10px;
    height: 10px;
  }

  .jvectormap-zoomin {
    top: 10px;
  }

  .jvectormap-zoomout {
    top: 30px;
  }

  .jvectormap-goback {
    bottom: 10px;
    z-index: 1000;
    padding: 6px;
  }

  .jvectormap-spinner {
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    background: center no-repeat url(data:image/gif;base64,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);
  }

  .jvectormap-legend-title {
    font-weight: bold;
    font-size: 14px;
    text-align: center;
  }

  .jvectormap-legend-cnt {
    position: absolute;
  }

  .jvectormap-legend-cnt-h {
    bottom: 0;
    right: 0;
  }

  .jvectormap-legend-cnt-v {
    top: 0;
    right: 0;
  }

  .jvectormap-legend {
    background: black;
    color: white;
    border-radius: 3px;
  }

  ul { list-style-type: none; }

  .jvectormap-legend-cnt-h .jvectormap-legend {
    float: left;
    margin: 0 10px 10px 0;
    padding: 3px 3px 1px 3px;
  }

  .jvectormap-legend-cnt-h .jvectormap-legend .jvectormap-legend-tick {
    float: left;
  }

  .jvectormap-legend-cnt-v .jvectormap-legend {
    margin: 10px 10px 0 0;
    padding: 3px;
  }

  .jvectormap-legend-cnt-h .jvectormap-legend-tick {
    width: 40px;
  }

  .jvectormap-legend-cnt-h .jvectormap-legend-tick-sample {
    height: 15px;
  }

  .jvectormap-legend-cnt-v .jvectormap-legend-tick-sample {
    height: 20px;
    width: 20px;
    display: inline-block;
    vertical-align: middle;
  }

  .jvectormap-legend-tick-text {
    font-size: 12px;
  }

  .jvectormap-legend-cnt-h .jvectormap-legend-tick-text {
    text-align: center;
  }

  .jvectormap-legend-cnt-v .jvectormap-legend-tick-text {
    display: inline-block;
    vertical-align: middle;
    line-height: 20px;
    padding-left: 3px;
  }
</style>
