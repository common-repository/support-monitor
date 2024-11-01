<template>
  <div class="home">
    <div class="flex flex-col">
      <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">

          <div class="mt-5 md:mt-0 mb-10 md:col-span-2">
              <div class="shadow sm:rounded-md sm:overflow-hidden">
                <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                  <div class="grid grid-cols-3 gap-6">
                    <div class="col-span-1 sm:col-span-1">
                      <label for="hour" class="block text-sm font-medium text-gray-700">
                        Unresolved Since (hour)
                      </label>
                      <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="number" min="0" v-model="hour" id="hour" class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300" placeholder="Enter hour, Ex: 24">
                      </div>
                    </div>
                    <div class="col-span-1 sm:col-span-1">
                      <label for="plugin" class="block text-sm font-medium text-gray-700">
                        Select Plugin
                      </label>
                      <div class="mt-1 flex rounded-md shadow-sm">
                        <select :disabled="searchBtnDisable" v-model="slug" id="plugin" class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300">
                          <option :value="null">All</option>
                          <option :value="plugin.slug" v-for="plugin in pluginList">{{ plugin.slug }}</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-span-1 sm:col-span-1">
                      <div class="mt-5 flex rounded-md shadow-sm">
                        <button :disabled="searchBtnDisable" type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" @click="getPluginsIssues">
                          Search
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
          </div>

          <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200" v-if="!loading">
              <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Plugin
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Topic
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Post Time
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Posted By
                </th>
              </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
              <template v-if="plugins.length > 0">
                <template v-for="plugin in plugins">
                  <template v-if="plugin.status === 'success' && plugin.issues.length > 1">
                    <tr v-for="(issue, index) in plugin.issues">
                      <td class="px-6 py-4 whitespace-nowrap" :rowspan="plugin.issues.length" v-if="index === 0">
                        <div class="text-sm text-gray-900">{{ plugin.slug }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <a :href="issue.link" target="_blank" class="text-sm text-gray-900">
                          <div v-html="issue.title"></div>
                        </a>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ issue.pubDate }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ issue.creator }}</div>
                      </td>
                    </tr>
                  </template>
                  <template v-else>
                    <tr>
                      <template v-if="plugin.status === 'success'">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-900">{{ plugin.slug }}</div>
                        </td>
                        <template v-if="plugin.issues.length > 0">
                          <template v-for="issue in plugin.issues">
                            <td class="px-6 py-4 whitespace-nowrap">
                              <a :href="issue.link" target="_blank" class="text-sm text-gray-900">
                                <div v-html="issue.title"></div>
                              </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                              <div class="text-sm text-gray-900">{{ issue.pubDate }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                              <div class="text-sm text-gray-900">{{ issue.creator }}</div>
                            </td>
                          </template>
                        </template>
                        <template v-else>
                          <td class="px-6 py-4 whitespace-nowrap" colspan="3">
                            <div class="text-sm text-gray-900">No Issue Found</div>
                          </td>
                        </template>

                      </template>
                      <template v-else>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-900">
                            {{ plugin.slug }}
                          </div>
                        </td>
                        <td colspan="3" class="px-6 py-4 whitespace-nowrap" >
                          <div class="text-sm text-gray-900">
                            {{ plugin.message }}
                          </div>
                        </td>
                      </template>
                    </tr>
                  </template>
                </template>
              </template>
              <tr v-else>
                <td colspan="4" class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 text-center">Not Enough Data To Show</div>
                </td>
              </tr>
              </tbody>
            </table>
            <ContentLoader viewBox="0 0 350 110" v-else>
              <rect x="5" y="10" rx="3" ry="3" width="350" height="10" />
              <rect x="20" y="30" rx="3" ry="3" width="350" height="10" />
              <rect x="20" y="50" rx="3" ry="3" width="350" height="10" />
              <rect x="5" y="70" rx="3" ry="3" width="250" height="10" />
              <rect x="20" y="90" rx="3" ry="3" width="350" height="10" />
              <rect x="20" y="110" rx="3" ry="3" width="250" height="10" />
            </ContentLoader>
          </div>
        </div>
      </div>
    </div>


  </div>
</template>

<script>
import apiFetch from '@wordpress/api-fetch';
import { ContentLoader } from 'vue-content-loader'

export default {
  name: 'UnresolvedIssues',
  components: {
    ContentLoader
  },
  data () {
    return {
      searchBtnDisable: false,
      loading: false,
      pluginList: [],
      plugins: [],
      slug: null,
      hour: 24
    }
  },
  methods: {
    getPluginsIssues() {
      this.loading = true;
      this.searchBtnDisable = true;
      let url = `/wp-json/supportmonitor/v1/api/issues?hour=${this.hour}`;
      if (this.slug) {
        url += `&slug=${this.slug}`;
      }

      apiFetch({ path: url }).then((resp) => {
        this.plugins = resp;
        this.loading = false;
        this.searchBtnDisable = false;
      }).catch(err => {
        if (err.code === 'rest_missing_callback_param') {
          this.$toastr.e("Failed to load data! " + err.message);
        }
        this.loading = false;
        this.searchBtnDisable = false;
      });
    },
    async getAllPlugins() {
      this.searchBtnDisable = true;
      await apiFetch({
        path: `/wp-json/supportmonitor/v1/api/plugins`,
      }).then((resp) => {
        this.pluginList = resp;
        this.searchBtnDisable = false;
      }).catch(err => {
        if (err.code === 'rest_missing_callback_param') {
          this.$toastr.e("Failed to load data! " + err.message);
        }
      });
    }
  },
  mounted() {
    this.getAllPlugins();
  }
}
</script>
