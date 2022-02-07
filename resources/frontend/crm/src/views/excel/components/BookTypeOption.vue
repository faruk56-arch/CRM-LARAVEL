<template>
  <div style="display:inline-block; margin-left: 10px;">
    <label class="radio-label">Format: </label>
    <el-select v-model="bookType" style="width:120px;" >
      <el-option
        v-for="item in options"
        :key="item"
        :label="item"
        :value="item"/>
    </el-select>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: String,
      default: 'xlsx'
    }
  },
  data() {
    return {
      options: ['xlsx', 'csv', 'pdf', 'txt']
    }
  },
  computed: {
    bookType: {
      get() {
        return this.value
      },
      set(val) {
        this.$emit('input', val)
      }
    }
  }
}
</script>
