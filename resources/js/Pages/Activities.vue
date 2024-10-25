<script setup>
import { onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";

const { props } = usePage();

onMounted(() => {
  console.log("activities", props.activities);
});
</script>

<template>
  <Head title="Atividades" />

  <AuthenticatedLayout :user="props.user">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Atividades
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div v-if="props.subjects.length">
              <ul>
                <li
                  v-for="subject in props.subjects"
                  :key="subject.id"
                  class="border-b border-gray-200 py-4"
                >
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-lg font-semibold">
                        <a :href="`/subjects/${subject.id}`">
                          {{ subject.name }} - {{ subject.course.name }}
                        </a>
                      </h3>
                      <p v-if="subject.next_review_at" class="text-gray-600">
                        Próxima revisão:
                        {{ new Date(subject.next_review_at).toLocaleString() }}
                      </p>
                    </div>
                    <div>
                      <span
                        v-if="subject.priority >= 8"
                        class="font-semibold text-red-500"
                      >
                        Prioridade alta
                      </span>
                      <span
                        v-else-if="subject.priority >= 5"
                        class="font-semibold text-yellow-500"
                      >
                        Prioridade Média
                      </span>
                      <span
                        v-if="subject.priority < 5"
                        class="font-semibold text-green-500"
                      >
                        Prioridade Baixa
                      </span>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
            <div v-else>
              <p>Nenhuma tarefa prioritária para hoje.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
