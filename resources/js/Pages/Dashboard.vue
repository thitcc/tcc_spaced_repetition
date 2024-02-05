<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const { props } = usePage();
const showCourseModal = ref(false);
const courseForm = useForm({
    name: ''
});

const showToast = ref(false);
const toastMessage = ref('');

function displayToast(message) {
    toastMessage.value = message;
    showToast.value = true;
    
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
}

function navigateToCourse(courseId) {
    window.location.href = `courses/${courseId}`;
}

function createCourse() {
    courseForm.post(route('courses.store'), {
        onSuccess: () => {
            showCourseModal.value = false;
            courseForm.reset('name');
            displayToast('Curso criado com sucesso!');
        }
    });
}
</script>


<style>
    .toast {
        position: fixed;
        left: 45%;
        bottom: 20px;
        background-color: #04AA6D;
        color: white;
        padding: 16px;
        border-radius: 8px;
        transition: all 0.5s ease;
        z-index: 1000;
        display: none;
    }
    .toast.show {
        display: block;
    }
</style>

<template>
    
    <Head title="Dashboard" />
    
    <AuthenticatedLayout>
        <div v-if="showToast" class="toast" :class="{ 'show': showToast }">{{ toastMessage }}</div>

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cursos</h2>
                <button v-if="props.userRole === 'teacher'" @click="showCourseModal = true" class="py-2 px-4 bg-blue-500 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none">
                    Adicionar Curso
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="props.courses.length" class="grid md:grid-cols-3 gap-4">
                            <div v-for="course in props.courses" :key="course.id" class="cursor-pointer bg-gray-100 hover:bg-gray-200 rounded-lg p-4 shadow transition duration-300 ease-in-out" @click="navigateToCourse(course.id)">
                                <h3 class="text-lg font-semibold text-gray-900">{{ course.name }}</h3>
                                <p class="text-sm text-gray-700">Professor: {{ course.teacher.name }}</p>
                            </div>
                        </div>
                        <div v-else>
                            Nenhum curso cadastrado.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showCourseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" id="my-modal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Adicionar Novo Curso</h3>
                    <form @submit.prevent="createCourse">
                        <input v-model="courseForm.name" type="text" placeholder="Nome do Curso" class="mt-2 px-3 py-2 border rounded-md w-full" required>
                        <div class="items-center px-4 py-3">
                            <button id="ok-btn" @click="createCourse" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                                Criar Curso
                            </button>
                        </div>
                        <div class="items-center px-4 py-3">
                            <button @click="showCourseModal = false" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>