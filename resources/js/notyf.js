import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

// Configuración global de Notyf
window.notyf = new Notyf({
    duration: 4000,
    position: {
        x: 'right',
        y: 'top'
    },
    dismissible: true,
    ripple: true,
    types: [
        {
            type: 'error',
            background: '#dc3545',
            duration: 6000,
            dismissible: true
        },
        {
            type: 'success',
            background: '#198754',
            duration: 3000,
            dismissible: true
        }
    ]
});
