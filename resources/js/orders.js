// JS para funcionalidades de órdenes
function ordersModuleActive() {
	return !!document.getElementById('orders-root');
}

if (typeof window !== 'undefined' && ordersModuleActive()) {
	console.log('Orders JS cargado');
}

// Aquí iría el resto del código específico de órdenes
