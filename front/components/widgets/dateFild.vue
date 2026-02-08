<template>
    <div class="jalali-date-picker" :class="{ 'is-focused': isFocused }" @focus="isFocused = true"
        @blur="isFocused = false">
        <!-- 1. روز (DD) -->
        <input type="text" v-model="dateParts.day" @input="handleInput('day', $event)" @blur="handleBlur('day', $event)"
            tabindex="1" @keydown="handleKeydown($event, 'day')" maxlength="2" ref="dayRef"
            @focus="$event.target.select()" />
        <span>/</span>

        <!-- 2. ماه (MM) -->
        <input type="text" v-model="dateParts.month" @input="handleInput('month', $event)" tabindex="2"
            @blur="handleBlur('month', $event)" @keydown="handleKeydown($event, 'month')" maxlength="2" ref="monthRef"
            @focus="$event.target.select()" />
        <span>/</span>

        <!-- 3. سال بخش اول (YY) -->
        <input type="text" v-model="dateParts.yearFirstHalf" @input="handleInput('yearFirstHalf', $event)" tabindex="3"
            @blur="handleBlur('yearFirstHalf', $event)" @keydown="handleKeydown($event, 'yearFirstHalf')" maxlength="2"
            ref="yearFirstHalfRef" @focus="$event.target.select()" />

        <!-- 4. سال بخش دوم (YY) -->
        <input type="text" v-model="dateParts.yearSecondHalf" @input="handleInput('yearSecondHalf', $event)"
            tabindex="4" @blur="handleBlur('yearSecondHalf', $event)" @keydown="handleKeydown($event, 'yearSecondHalf')"
            maxlength="2" ref="yearSecondHalfRef" @focus="$event.target.select()" />
    </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';

const props = defineProps({
    date: {
        type: String, // انتظار "YYYY/MM/DD"
        default: new Date().toLocaleDateString('fa-IR')
    }
});

const emit = defineEmits(['update:modelValue', 'update:dateObject']);

const dayRef = ref(null);
const monthRef = ref(null);
const yearFirstHalfRef = ref(null);
const yearSecondHalfRef = ref(null);
const isFocused = ref(false);

const toEnglishDigits = (str) => {
    if (!str) return '';

    const persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    const arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    return str.toString().replace(/[۰-۹]/g, (d) => {
        return persianNumbers.indexOf(d);
    }).replace(/[٠-٩]/g, (d) => {
        return arabicNumbers.indexOf(d);
    })
};
const defaultDay = toEnglishDigits(props.date).split('/')
console.log(props.date)
const dateParts = ref({
    yearFirstHalf: defaultDay[0].substring(2, 4),
    yearSecondHalf: defaultDay[0].substring(0, 2),
    month: defaultDay[1].padStart(2, 0),
    day: defaultDay[2].padStart(2, 0)
});

// --- تابع کمکی: پدینگ و اعتبارسنجی (شامل محدودسازی) ---
const applyPaddingAndValidate = (partName, currentValue) => {
    let value = currentValue.replace(/[^0-9]/g, ''); // فقط اعداد

    if (partName === 'day') {
        const dayInt = parseInt(value, 10);

        if (value.length > 2) {
            value = value.substring(0, 2); // اگر بیشتر از 2 کاراکتر وارد شد (با کپی/پیست)
        }

        if (value && dayInt > 31) {
            value = '31'; // محدود به 31
        } else if (value.length === 1) {
            value = value.padStart(2, '0'); // پدینگ 2 رقمی
        }
    }
    else if (partName === 'month') {
        const monthInt = parseInt(value, 10);

        if (value.length > 2) {
            value = value.substring(0, 2);
        }

        if (value && monthInt > 12) {
            value = '12'; // محدود به 12
        } else if (value.length === 1) {
            value = value.padStart(2, '0'); // پدینگ 2 رقمی
        }
    }
    else if (partName === 'yearSecondHalf') {
        const yearSecondHalf = parseInt(value, 10);

        if (value.length > 2) {
            value = value.substring(0, 2);
        }

        if (value && (yearSecondHalf > 14 || yearSecondHalf < 13)) {
            value = '14';
        } else if (value.length === 1) {
            value = value.padStart(2, '0'); // پدینگ 2 رقمی
        }
    }
    else if (partName === 'yearFirstHalf') {
        const yearFirstHalf = parseInt(value, 10);

        if (value.length > 2) {
            value = value.substring(0, 2);
        }

        if (value && yearFirstHalf > 99) {
            value = '99';
        } else if (value.length === 1) {
            value = value.padStart(2, '0'); // پدینگ 2 رقمی
        }
    }

    return value;
};

// --- 1. مدیریت ورودی (فقط اجازه ورود و حذف کاراکتر) ---
const handleInput = (partName, event) => {
    let value = event.target.value.replace(/[^0-9]/g, ''); // فقط اعداد

    // در حین تایپ، اجازه ورود را می‌دهیم اما پدینگ/اعتبارسنجی را انجام نمی‌دهیم
    dateParts.value[partName] = value;

    emitUpdate();
};


// --- 2. مدیریت خروج از فیلد (Blur) - اجرای پدینگ و اعتبارسنجی/محدودسازی ---
const handleBlur = (partName, event) => {
    const rawValue = dateParts.value[partName];

    if (!rawValue) {
        return;
    }

    // اجرای منطق جدید: پدینگ و محدودسازی سقف مجاز
    const validatedAndPaddedValue = applyPaddingAndValidate(partName, rawValue);

    // به‌روزرسانی مقدار نهایی در حالت داخلی
    dateParts.value[partName] = validatedAndPaddedValue;

    emitUpdate();
};


// --- 3. مدیریت کلیدها (برای ناوبری چپ/راست و Backspace) ---
const handleKeydown = (event, partName) => {
    const { key, target } = event;
    const val = event.target.value
    // ترتیب فیلدها از چپ به راست در DOM: Day, Month, Year1, Year2
    const currentRefs = {
        'day': { next: monthRef.value, prev: null },
        'month': { next: yearFirstHalfRef.value, prev: dayRef.value },
        'yearFirstHalf': { next: yearSecondHalfRef.value, prev: monthRef.value },
        'yearSecondHalf': { next: null, prev: yearFirstHalfRef.value }
    };

    if (key === 'Enter' || key === 'ArrowLeft' || key === 'ArrowRight') {
        event.preventDefault();
        let nextFocusRef = null;

        if (key === 'Enter' || key === 'ArrowLeft' && currentRefs[partName].next) {
            if (key === 'Enter') {
                const rawValue = dateParts.value[partName];
                if (!rawValue) {
                    return;
                }
                const validatedAndPaddedValue = applyPaddingAndValidate(partName, rawValue);
                dateParts.value[partName] = validatedAndPaddedValue;
            }

            nextFocusRef = currentRefs[partName].next;
        } else if (key === 'ArrowRight' && currentRefs[partName].prev) {
            nextFocusRef = currentRefs[partName].prev;
        }

        if (nextFocusRef) {
            nextTick(() => nextFocusRef.focus());
        }
        return
    }

    // در صورت فشردن Backspace روی اولین کاراکتر، به فیلد قبل برو
    else if (key === 'Backspace') {
        const prevRef = currentRefs[partName].prev;
        if (prevRef && val.length === 0) {
            event.preventDefault();
            // اضافه کردن تأخیر کوچک برای اطمینان از به‌روزرسانی DOM قبل از فوکوس مجدد
            nextTick(() => prevRef.focus());
        }
        return
    }

    else if (event.target.value.length == 2) {
        const nextFocusRef = currentRefs[partName].next;
        nextTick(() => nextFocusRef.focus());
        return
    }
};


const emitUpdate = () => {
    const { yearFirstHalf, yearSecondHalf, month, day } = dateParts.value;

    if (yearFirstHalf.length === 2 && yearSecondHalf.length === 2 && month.length === 2 && day.length === 2) {
        const fullDateString = `${yearFirstHalf}${yearSecondHalf}/${month}/${day}`;
        emit('update:modelValue', fullDateString);
        emit('update:dateObject', { year: `${yearFirstHalf}${yearSecondHalf}`, month, day });
    } else {
        emit('update:modelValue', '');
        emit('update:dateObject', {});
    }
};

// watch(() => props.modelValue, (newVal) => {
//     if (newVal && newVal.includes('/') && newVal.length === 10) {
//         const yearPart = newVal.substring(0, 4);
//         const monthPart = newVal.substring(5, 7);
//         const dayPart = newVal.substring(8, 10);

//         dateParts.value.yearFirstHalf = yearPart.substring(0, 2);
//         dateParts.value.yearSecondHalf = yearPart.substring(2, 4);
//         dateParts.value.month = monthPart;
//         dateParts.value.day = dayPart;
//     } else if (!newVal) {
//         dateParts.value = { yearFirstHalf: '', yearSecondHalf: '', month: '', day: '' };
//     }
// }, { immediate: true });

</script>

<style scoped>
.jalali-date-picker {
    display: flex;
    flex-direction: row;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 0 5px;
    background-color: white;
    transition: border-color 0.2s;
    outline: none;
}

.jalali-date-picker:focus-within {
    border-color: var(--primary-color, #007bff);
    box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
}

.jalali-date-picker span {
    display: flex;
    align-items: center;
    padding: 0 2px;
    color: #6c757d;
}

.jalali-date-picker input {
    border: none;
    text-align: center;
    padding: 5px 0;
    outline: none;
    background: transparent;
    font-size: 1rem;
}

.jalali-date-picker input {
    width: 30%;
}

.jalali-date-picker input:nth-child(5) {
    width: 20%;
}

.jalali-date-picker input:nth-child(6) {
    width: 20%;
}
</style>