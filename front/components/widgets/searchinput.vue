<template>
    <div class="" style="gap: 10px;">
        <div class="w-100">
            <input type="text" class="form-control text-center" v-model="searchableID" :placeholder="props.placeholder"
                @keyup="searchById" @keydown.enter.prevent @focus="$event.target.select()" :disabled="disabled">
        </div>
        <!-- <div class="w-100" :class="searchResultLable ? 'pt-1' : ''">
            <span v-html="searchResultLable"></span>
        </div> -->
    </div>

    <div class="searchByTextDialog" v-if="searchByTextDialog && props.textSearchUrl != '0'">
        <div class="shadow" @click="searchByTextDialog = false"></div>
        <div class="dialogBody">
            <div class="header">
                <input type="text" class="form-control" v-model="searchByTextFilde" ref="inputRef" @keyup="searchByText" @keydown.enter.prevent
                    :autofocus="searchByTextDialog">
            </div>
            <div class="body">

                <table>
                    <thead>
                        <tr v-if="loading">
                            <th :colspan="props.columns.length + 1" class="text-center">
                                <div class="w-100 text-center d-flex"
                                    style="flex-direction: column;align-items: center;">
                                    <video src="/public/LoadingCat.webm" autoplay muted loop></video>
                                    درحال بارگزاری ...
                                </div>
                            </th>
                        </tr>
                        <tr v-else-if="baseDatas.data">
                            <th>#</th>
                            <th v-for="th in props.columns">{{ th.label }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="searchResultLable && !baseDatas.data">
                            <td :colspan="props.columns.length + 1" class="text-center text-danger"
                                v-html="searchResultLable"></td>
                        </tr>
                        <tr v-else v-for="(val, index) in baseDatas.data" @click="selectRow(val)"
                            :class="{ 'selected-row': selectedIndex === index }" :key="val.id">
                            <td>
                                <span v-if="selectedIndex === index"><input type="checkbox" checked=""></span>
                                <span v-else>{{ val.id }}</span>
                            </td>
                            <th v-for="th in props.columns">
                                {{ th.render ? th.render(val) : resolveValue(val, th.key) }}
                            </th>


                        </tr>
                    </tbody>
                </table>

            </div>
            <div class="footer"></div>
        </div>
    </div>

</template>

<script setup>
import { nextTick, watch } from 'vue'
// const emit = defineEmits(['searchableID'])
const emit = defineEmits(['update:modelValue'])

const props = defineProps({
    modelValue: {
        type: [Number, String, Object, null],
        default: null
    },
    def: {
        type: Number, // number | string | uuid
        default: null
    },
    placeholder: {
        type: String,
        default: 'جستجو'
    },
    textSearchUrl: {
        type: String,
        default: '/'
    },
    idSearchUrl: {
        type: String,
        default: '/'
    },
    methode: {
        type: String,
        default: 'POST'
    },
    columns: {
        type: Array,
        default: [
            { label: 'عنوان', key: 'title' },
            { label: 'توضیحات', key: 'des' },
        ]
    },
    querySearch: {
        type: Object,
        default: {}
    },
    disabled: {
        type: Boolean,
        default: false
    },
    // searchResultLable: {
    //     type: Function,
    //     default: r => r.id || ''
    // }
});


const { $api } = useNuxtApp()
const config = useRuntimeConfig()
const baseUrl = config.public.apiBase
const loading = ref(false)
const previousIdValue = ref('')
const previousTextValue = ref('')
const searchByTextDialog = ref(false)
// const searchableID = ref(props.def);
const searchableID = ref(props.modelValue && typeof props.modelValue === 'object' ? props.modelValue.id : props.modelValue)
const searchResultLable = ref(null);
const paginatePage = ref(1);
const paginate = ref([]);
const searchByTextFilde = ref('');
const inputRef = ref(null)
const baseDatas = ref({});


let keyupdelay = 0
const searchById = async (e) => {
    emit('update:modelValue', searchableID.value)

    clearTimeout(keyupdelay)
    const si = searchableID.value;
    const key = e.which || e.keyCode || 0;
    
    // Prevent form submission when Enter is pressed
    if (key == 13) {
        e.preventDefault()
        e.stopPropagation()
    }
    
    if (previousIdValue.value === si && key != 13) return
    previousIdValue.value = si
    searchResultLable.value = ''
    if (!si) return

    if (isNaN(si)) {
        if (key == 8) return
        if (props.textSearchUrl != '0') {
            searchByTextDialog.value = true
            searchByTextFilde.value = si
        }
        searchableID.value = ''
        emit('update:modelValue', searchableID.value)
        return
    }

    let delay = 800
    if (key == 13) delay = 0
    keyupdelay = setTimeout(async () => {
        loading.value = true
        searchResultLable.value = '<span class="text-muted"><p class="text-danger m-0">درحال جست و جو ....</p></span>'
        emit('update:modelValue', 'درحال جست و جو ....')
        try {
            const response = await $api(props.idSearchUrl + si, {
                method: 'POST',
                body: { ...props.querySearch }
            })
            console.log(response)
            if (!response) {
                searchResultLable.value = `<span class="text-muted"><p class="text-danger m-0">خطایی رخ داده</p></span>`
                searchableID.value = ''
                emit('update:modelValue', 'یافت نشد !!!')
                return
            }
            else if (!response.status || response.status != 'success') {
                if (!response.data || !!response.data.length) {
                    searchResultLable.value = `<span class="text-muted"><p class="text-danger m-0">موردی یافت نشد</p></span>`
                    searchableID.value = ''
                    emit('update:modelValue', 'یافت نشد !!!')
                    return
                }
                const text = response.message || 'خطایی رخ داده'
                searchResultLable.value = `<span class="text-muted"><p class="text-danger m-0">${text}</p></span>`
                searchableID.value = ''
                return
            }

            const cu = response.data
            searchResultLable.value = ''
            emit('update:modelValue', cu)
            searchableID.value = cu.id
        } catch (error) {
            searchResultLable.value = `<span class="text-muted"><p class="text-danger m-0">خطایی رخ داده</p></span>`
            emit('update:modelValue', 'یافت نشد !!!')
        }
        loading.value = false
    }, delay);

}

const searchByText = async (e) => {
    clearTimeout(keyupdelay)
    const sbtfv = searchByTextFilde.value
    const key = e.which || e.keyCode || 0;
    
    // Prevent form submission when Enter is pressed
    if (key == 13) {
        e.preventDefault()
        e.stopPropagation()
    }
    
    if (previousTextValue.value === sbtfv && key != 13) return
    previousTextValue.value = sbtfv
    baseDatas.value = []
    if (!sbtfv) return

    let delay = 800
    if (key == 13) delay = 0
    keyupdelay = setTimeout(async () => {
        loading.value = true
        try {
            const response = await $api(props.textSearchUrl, {
                method: 'POST',
                body: { values: sbtfv, ...props.querySearch }
            })
            console.log(response)
            loading.value = false
            if (!response) {
                searchResultLable.value = `<p class="text-danger m-0">خطایی رخ داده</p>`
                return
            }
            else if (response && (!response.status || response.status != 'success')) {
                const text = response.message || 'خطایی رخ داده'
                searchResultLable.value = `<p class="text-danger m-0">${text}</p>`
                return
            }
            else if (!response.items.data || !response.items.data.length) {
                searchResultLable.value = `<p class="text-danger m-0">موردی یافت نشد</p>`
                return
            }
            searchResultLable.value = null
            baseDatas.value = response.items
        } catch (error) {
            searchResultLable.value = `<p class="text-danger m-0">خطایی رخ داده</p>`
            loading.value = false
        }
    }, delay);
}
const selectRow = async (val) => {
    searchResultLable.value = ''
    emit('update:modelValue', val)
    searchableID.value = val.id
    searchByTextDialog.value = false
}

const selectedIndex = ref(-1)
const tableRows = ref([])
const handleDialogKeydown = (e) => {
    if (!baseDatas.value || !baseDatas.value.data || !baseDatas.value.data.length) return
    const key = e.key

    switch (key) {
        case 'ArrowDown':
            e.preventDefault()
            if (selectedIndex.value < baseDatas.value.data.length - 1) {
                selectedIndex.value++
                // scrollToSelectedRow()
            }
            break

        case 'ArrowUp':
            e.preventDefault()
            if (selectedIndex.value > 0) {
                selectedIndex.value--
                // scrollToSelectedRow()
            }
            break

        case 'Enter':
            e.preventDefault()
            e.stopPropagation()
            if (selectedIndex.value >= 0 && baseDatas.value.data[selectedIndex.value]) {
                selectRow(baseDatas.value.data[selectedIndex.value])
            }
            break


    }
}


const resolveValue = (obj, path) => {
    if (!obj || !path) return ''

    return path
        .split('.')
        .reduce((acc, key) => acc?.[key], obj) ?? ''
}


const keydownHandler = e => {
    if (searchByTextDialog.value) handleDialogKeydown(e)
}

onMounted(() => {
    window.addEventListener('keydown', keydownHandler)
})

onUnmounted(() => {
    window.removeEventListener('keydown', keydownHandler)
})


watch(searchByTextDialog, (newVal) => {
    if (newVal) {
        nextTick(() => {
            if (inputRef.value) {
                inputRef.value.focus();
            }
        })
    }
})

watch(() => props.modelValue, (val) => {
    if (val && typeof val === 'object') {
        searchableID.value = val.id
    } else {
        searchableID.value = val
    }
})
</script>

<style scoped>
/* --- استایل‌های دیالوگ (تکمیل شده) --- */
.searchByTextDialog {
    position: fixed;
    width: 100%;
    height: 100%;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    z-index: 99999;
    display: flex;
    justify-content: center;
    align-items: center;
    background: rgba(0, 0, 0, 0.4);
}

.shadow {
    position: absolute;
    width: 100%;
    height: 100%;
    background: transparent;
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
}

.dialogBody {
    position: relative;
    width: 80%;
    max-width: 700px;
    height: 80%;
    max-height: 600px;
    background-color: #ffffff;
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
}

.header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
}

.header .form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    box-sizing: border-box;
}

.body {
    flex-grow: 1;
    overflow-y: auto;
    padding: 0 20px;
}

.footer {
    padding: 10px 20px;
    border-top: 1px solid #eee;
}

.body table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.body thead {
    background-color: #f8f9fa;
    position: sticky;
    top: 0;
    z-index: 10;
    box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
}

.body th,
.body td {
    padding: 12px 15px;
    text-align: right;
    border-bottom: 1px solid #dee2e6;
}

.body th {
    color: #495057;
    font-weight: 600;
    text-transform: uppercase;
}

.body tbody tr:hover {
    background-color: #e9ecef;
    cursor: pointer;
}


.selected-row {
    background-color: var(--bs-info-bg-subtle);
    box-shadow: 0 0 0 1px #c1c1c1;
    border-radius: 10px;
}

.selected-row td {
    border: none !important;
    font-weight: bolder !important;
}

.selected-row td:first-child {
    border-radius: 0 11px 11px 0;
    /* box-shadow: 1px 0px 0 1px #c1c1c1; */
}

.selected-row td:last-child {
    border-radius: 11px 0 0 11px;
    /* box-shadow: -1px 0px 0 1px #c1c1c1; */
}
</style>
