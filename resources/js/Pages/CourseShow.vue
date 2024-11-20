<script setup>
import { ref, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";

const { props } = usePage();
const showSubjectModal = ref(false);
const showAddStudentModal = ref(false);

const availableStudents = ref([]);
const selectedStudents = ref([]);
const searchQuery = ref("");
const isLoading = ref(false);

var subjectForm = useForm({
  name: "",
  time_limit: null,
  course_id: null,
});

const studentForm = useForm({
  student_ids: [],
  course_id: props.course.id,
});

function navigateToSubject(subjectId) {
  window.location.href = `/subjects/${subjectId}`;
}

async function searchStudents() {
  isLoading.value = true;
  try {
    const response = await axios.get(
      `/courses/${props.course.id}/available-students`,
      {
        params: { search: searchQuery.value },
      }
    );
    availableStudents.value = response.data;
  } catch (error) {
    console.error("Error fetching students:", error);
  }
  isLoading.value = false;
}

function createSubject() {
  subjectForm.course_id = props.course.id;

  subjectForm.post(route("subjects.store"), {
    onSuccess: () => {
      showSubjectModal.value = false;
      subjectForm.reset();
    },
  });
}

function addStudents() {
  studentForm.student_ids = selectedStudents.value.map((student) => student.id);

  studentForm.post(route("courses.addStudent", props.course.id), {
    onSuccess: () => {
      showAddStudentModal.value = false;
      selectedStudents.value = [];
      studentForm.reset();
    },
  });
}

watch(showAddStudentModal, (newValue) => {
  if (newValue) {
    searchStudents();
  }
});
</script>

<template>
  <AuthenticatedLayout :user="props.user">
    <Head title="Detalhes do Curso" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div
          class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex justify-between items-center p-6"
        >
          <div>
            <h2 class="text-2xl font-semibold">{{ props.course.name }}</h2>
            <p class="text-lg">Professor: {{ props.course.teacher.name }}</p>
          </div>
          <div>
            <button
              v-if="props.userRole === 'teacher'"
              @click="showSubjectModal = true"
              class="py-2 px-4 bg-blue-500 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none"
            >
              Adicionar Tópico
            </button>
            <button
              v-if="props.userRole === 'teacher'"
              @click="showAddStudentModal = true"
              class="py-2 px-4 bg-green-500 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 focus:outline-none ml-2"
            >
              Adicionar Aluno
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="showSubjectModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50 backdrop-blur-sm"
    >
      <div class="bg-white p-8 rounded-lg shadow-lg w-1/3 pointer-events-auto">
        <h3 class="font-semibold text-xl mb-4">Adicionar Tópico</h3>
        <form @submit.prevent="createSubject">
          <div class="mb-4">
            <label
              class="block text-gray-700 text-sm font-bold mb-2"
              for="name"
            >
              Nome do Tópico *
            </label>
            <input
              v-model="subjectForm.name"
              type="text"
              id="name"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              required
            />
          </div>
          <div class="flex items-center justify-between">
            <button
              class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
              @click="showSubjectModal = false"
            >
              Cancelar
            </button>
            <button
              class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
              type="submit"
            >
              Adicionar
            </button>
          </div>
        </form>
      </div>
    </div>

    <div
      v-if="showAddStudentModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50 backdrop-blur-sm"
    >
      <div class="bg-white p-8 rounded-lg shadow-lg w-2/3 pointer-events-auto">
        <h3 class="font-semibold text-xl mb-4">Adicionar Alunos</h3>
        <div class="mb-4">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar por nome ou email..."
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          />
        </div>

        <div class="max-h-80 overflow-y-auto border rounded-lg p-2">
          <div v-if="isLoading" class="text-center py-4">Carregando...</div>
          <template v-else>
            <div
              v-for="student in availableStudents"
              :key="student.id"
              class="flex items-center p-2 hover:bg-gray-100 rounded"
            >
              <input
                type="checkbox"
                :value="student"
                v-model="selectedStudents"
                class="mr-3"
              />
              <div>
                <div class="font-semibold">{{ student.name }}</div>
                <div class="text-sm text-gray-600">{{ student.email }}</div>
              </div>
            </div>
          </template>
        </div>

        <div class="flex items-center justify-between mt-4">
          <button
            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
            @click="showAddStudentModal = false"
          >
            Cancelar
          </button>
          <button
            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
            @click="addStudents"
            :disabled="!selectedStudents.length"
          >
            Adicionar ({{ selectedStudents.length }})
          </button>
        </div>
      </div>
    </div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div
        v-for="subject in props.course.subjects"
        :key="subject.id"
        class="cursor-pointer bg-white rounded-xl p-6 mb-5 shadow-md transition duration-300 ease-in-out transform hover:scale-105 hover:shadow-lg"
        @click="navigateToSubject(subject.id)"
      >
        <h3 class="text-xl font-semibold text-gray-800">{{ subject.name }}</h3>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
