import Vue from 'vue'
import Router from 'vue-router'
import UnresolvedIssues from 'admin/pages/UnresolvedIssues.vue'
import Plugins from 'admin/pages/Plugins.vue'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      redirect: '/unresolved-issues',
    },
    {
      path: '/unresolved-issues',
      name: 'UnresolvedIssues',
      component: UnresolvedIssues
    },
    {
      path: '/plugins',
      name: 'Plugins',
      component: Plugins
    },
  ]
})
