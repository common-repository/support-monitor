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
                      <label for="plugin" class="block text-sm font-medium text-gray-700">
                       New Plugin Slug
                      </label>
                      <div class="mb-6">
                        <input type="text" v-model="slug" id="plugin" class="shadow appearance-none border border-red-500 rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter slug, Ex: elementor">
                        <br>
                        <p class="text-red-500 text-xs italic" v-if="errors && errors.slug">{{ errors.slug }}</p>
                      </div>
                    </div>
                    <div class="col-span-1 sm:col-span-1">
                      <div class="mt-5 flex rounded-md shadow-sm">
                        <button type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" @click="store">
                          Save
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
          </div>

          <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  #
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Plugin
                </th>
              </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
              <template v-if="plugins.length > 0">
                <tr v-for="plugin in plugins">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ plugin.ID }}
                      </div>
                    </td>
                    <td colspan="3" class="px-6 py-4 whitespace-nowrap" >
                      <div class="text-sm text-gray-900">
                        {{ plugin.slug }}
                      </div>
                    </td>
                </tr>
              </template>
              <tr v-else>
                <td colspan="4"><h3 class="text-center">Not Enough Data To Show</h3></td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>


  </div>
</template>

<script>
import apiFetch from '@wordpress/api-fetch';

export default {
  name: 'UnresolvedIssues',
  data () {
    return {
      plugins: [],
      slug: '',
      errors: null,
    }
  },
  methods: {
    getAllPlugins() {
      let url = `/wp-json/supportmonitor/v1/api/plugins`;

      apiFetch({ path: url }).then((resp) => {
        this.plugins = resp;
      }).catch(err => {
        if (err.code === 'rest_missing_callback_param') {
          this.$toastr.e("Failed to load data! " + err.message);
        }
      });
    },
    store() {
      let url = `/wp-json/supportmonitor/v1/api/plugins`;

      apiFetch({
        path: url,
        method: 'POST',
        data: { slug: this.slug }
      }).then((resp) => {
        this.$toastr.s("Plugin record saved successfully");
        this.getAllPlugins();
      }).catch(err => {
        if (err.code === 'rest_invalid_param') {
          this.$toastr.e("Missing required data! " + err.message);
          this.errors = err.data.params;
        }
      });
    }
  },
  mounted() {
    this.getAllPlugins();
  }
}
</script>
