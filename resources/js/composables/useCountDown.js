import { ref, onMounted, onUnmounted } from 'vue';
import dayjs from 'dayjs';
import duration from 'dayjs/plugin/duration';

dayjs.extend(duration);

export default function useCountdown() {
    const now = ref(dayjs());
    let interval = null;

    onMounted(() => {
        interval = setInterval(() => {
            now.value = dayjs();
        }, 1000);
    });

    onUnmounted(() => {
        clearInterval(interval);
    })

    const isExpired = (date) => {
        return dayjs(date).isBefore(now.value);
    }

    const timeLeft = (date) => {
        const diff = dayjs(date).diff(now.value);

        if (diff <= 0) return 'Expired';

        const d = dayjs.duration(diff);

        const totalHours = Math.floor(d.asHours());
        const days = Math.floor(d.asDays());
        const hours = d.hours();
        const minutes = d.minutes();
        const seconds = d.seconds();
        
        if (totalHours < 24) {
            if (totalHours > 0) {
                return `${totalHours}h ${minutes}m`;
            }

            return `${minutes}m ${seconds}s`;
        }

        return `${days}d ${hours}h`;
    }

    return {
        now,
        isExpired,
        timeLeft
    }
}