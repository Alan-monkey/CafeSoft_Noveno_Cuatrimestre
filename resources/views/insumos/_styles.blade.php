<style>
/* ===== CONTENEDOR PRINCIPAL ===== */
.inventario-container {
    position: relative;
    min-height: 100vh;
    background: linear-gradient(145deg, #faf0e6, #f5e6d3);
    font-family: 'Poppins', 'Segoe UI', sans-serif;
    padding: 20px 0;
}

/* ===== HEADER ===== */
.inventario-header {
    background: linear-gradient(135deg, #8B4513, #A0522D);
    color: white;
    padding: 25px 30px;
    display: flex;
    align-items: center;
    gap: 20px;
    position: relative;
    overflow: hidden;
    border-radius: 30px 30px 0 0;
    flex-wrap: wrap;
}

.inventario-header::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 200px; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.2));
    transform: skewX(-20deg) translateX(100px);
    animation: shine 3s infinite;
}

@keyframes shine {
    0%   { transform: skewX(-20deg) translateX(100px); }
    20%  { transform: skewX(-20deg) translateX(-200px); }
    100% { transform: skewX(-20deg) translateX(-200px); }
}

.header-icon {
    width: 60px; height: 60px;
    background: rgba(255,255,255,.2);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.header-title h4 { margin: 0; font-weight: 700; font-size: 1.5rem; }
.header-title p  { margin: 5px 0 0; opacity: .9; font-size: .9rem; }

.coffee-decoration-header span {
    margin: 0 5px;
    animation: bounce 2s infinite;
    display: inline-block;
    font-size: 1.5rem;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-5px); }
}

/* ===== BOTÓN HEADER ===== */
.btn-nuevo {
    background: rgba(255,255,255,.2);
    color: white;
    border: 1px solid rgba(255,255,255,.3);
    padding: 10px 20px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    transition: all .3s;
}
.btn-nuevo:hover {
    background: #D4AF37;
    color: #2c1a0b;
}

/* ===== TARJETA PRINCIPAL ===== */
.inventario-card {
    background: rgba(255,255,255,.98);
    border-radius: 0 0 30px 30px;
    padding: 35px;
    box-shadow: 0 20px 40px rgba(139,69,19,.15);
}

/* ===== FORMULARIO ===== */
.form-label {
    font-weight: 600;
    color: #5D4037;
    margin-bottom: 5px;
    display: block;
}

.form-control {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid #e8d5c0;
    border-radius: 10px;
    font-family: 'Poppins', sans-serif;
    transition: all .3s;
}
.form-control:focus {
    outline: none;
    border-color: #8B4513;
    box-shadow: 0 0 0 4px rgba(139,69,19,.1);
}
.form-control.is-invalid { border-color: #dc3545; }

.invalid-feedback {
    color: #dc3545;
    font-size: .85rem;
    margin-top: 4px;
}

/* ===== BOTONES DEL FORMULARIO ===== */
.btn-guardar {
    background: linear-gradient(145deg, #8B4513, #A0522D);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: all .3s;
    font-family: 'Poppins', sans-serif;
}
.btn-guardar:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(139,69,19,.3);
}

.btn-cancel-form {
    background: #f0e4d5;
    color: #8B4513;
    border: none;
    padding: 12px 25px;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    transition: all .3s;
}
.btn-cancel-form:hover {
    background: #e0d0be;
    color: #8B4513;
}
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">