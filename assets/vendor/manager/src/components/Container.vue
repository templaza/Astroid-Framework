<script setup>
import Sidebar from './Sidebar.vue'
import Main from './Main.vue'
import { onMounted, ref } from 'vue';
const props = defineProps({
  config: { type: Object, default: null },
});

const pageIndex     = ref(new Object());
const fieldSet_tabs = ref(new Object());

onMounted(() => {
  props.config.astroid_content.forEach((fieldSet, idx) => {
    if (idx === 0) {
      pageIndex.value[fieldSet.name] = 'd-block';
    } else {
      pageIndex.value[fieldSet.name] = 'd-none';
    }
    fieldSet_tabs.value[fieldSet.name] = Object.keys(fieldSet.childs)[0];
  });
})

function pageActive(pgIndex, group = null) {
  props.config.astroid_content.forEach(fieldSet => {
    pageIndex.value[fieldSet.name] = 'd-none';
  });
  pageIndex.value[pgIndex] = 'd-block';
  setTimeout(function () {
    if (group !== null) {
      const el = document.getElementById('astroid-page-'+group);
      const y = el.getBoundingClientRect().top + window.scrollY - 90;
      window.scrollTo({top: y, behavior: 'smooth'});
      if (typeof fieldSet_tabs.value[pgIndex] !== 'undefined') {
        fieldSet_tabs.value[pgIndex] = group;
      }
    } else {
      window.scrollTo({top: 0, behavior: 'smooth'});
    }
  }, 100);
}

</script>
<template>
  <div class="container-xxl as-gutter mt-3 my-md-4 as-layout">
    <Sidebar :config="props.config" @sidebar-active="pageActive" />
    <Main :config="props.config" :page-index="pageIndex" :field-set_tabs="fieldSet_tabs" />
  </div>
</template>