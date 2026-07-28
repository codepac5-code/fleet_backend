<x-master-layout>

<div class="stg-container">

<style>

:root {
    --stg-primary: #312873;
    --stg-accent: #FCB902;
    --stg-border: #ececf3;
    --stg-muted: #6c6c7a;
}

/* Container */
.stg-container {
    font-family: 'Cairo', sans-serif;
}

/* Layout */
.stg-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 55px;
    align-items: start;
}

/* Sidebar */
.stg-sidebar {
    position: sticky;
    top: 30px;
}

.stg-sidebar-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--stg-primary);
    margin-bottom: 25px;
}

/* Menu */
.stg-menu {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.stg-menu-item {
    padding: 14px 18px;
    border-radius: 14px;
    cursor: pointer;
    transition: 0.25s ease;
    color: var(--stg-muted);
    font-weight: 500;
    position: relative;
}

/* Hover */
.stg-menu-item:hover {
    background: rgba(49,40,115,0.05);
    color: var(--stg-primary);
}

/* Active */
.stg-menu-item.stg-active {
    background: rgba(49,40,115,0.07);
    color: var(--stg-primary);
    font-weight: 600;

    box-shadow: inset -4px 0 0 var(--stg-accent);
}

/* Main */
.stg-main {
    position: relative;
}

/* Soft separation line */
.stg-main::before {
    content: "";
    position: absolute;
    right: -30px;
    top: 10px;
    bottom: 10px;
    width: 1px;
    background: linear-gradient(
        to bottom,
        transparent,
        rgba(49,40,115,0.15),
        transparent
    );
}

/* Card */
.stg-card {
    background: #fff;
    border-radius: 20px;
    padding: 45px;
    border: 1px solid var(--stg-border);
    box-shadow: 0 12px 40px rgba(49,40,115,0.04);
    min-height: 450px;
    position: relative;
}

/* Top subtle accent bar */
.stg-card::before {
    content: "";
    position: absolute;
    top: 0;
    right: 0;
    height: 5px;
    width: 120px;
    background: linear-gradient(
        90deg,
        var(--stg-primary),
        var(--stg-accent)
    );
    border-top-right-radius: 20px;
}

/* Header */
.stg-card-header {
    margin-bottom: 35px;
}

.stg-card-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--stg-primary);
}

/* Responsive */
@media(max-width: 992px){
    .stg-layout {
        grid-template-columns: 1fr;
        gap: 30px;
    }

    .stg-main::before {
        display: none;
    }
}

</style>

<div class="stg-layout">

    <!-- Sidebar -->
    <div class="stg-sidebar">
        <div class="stg-sidebar-title">{{ __('messages.settings') }}</div>

        <div class="stg-menu">
            <div class="stg-menu-item stg-active"
                 data-url="{{ route('settings.general') }}">
                {{ __('messages.general_settings') }}
            </div>

            <div class="stg-menu-item"
                 data-url="{{ route('settings.general') }}">
                {{ __('messages.system_settings') }}
            </div>

            <div class="stg-menu-item"
                 data-url="{{ route('settings.general') }}">
                {{ __('messages.notifications') }}
            </div>

            <div class="stg-menu-item"
                 data-url="{{ route('settings.general') }}">
                {{ __('messages.security') }}
            </div>
            <div class="stg-menu-item"
                 data-url="{{ route('settings.general') }}">
                {{ __('messages.region_settings') }}
            </div>

            <div class="stg-menu-item"
                 data-url="{{ route('settings.currencies') }}">
                {{ app()->getLocale() === 'ar' ? 'أسعار الصرف' : 'Exchange rates' }}
            </div>
        </div>
    </div>

    <!-- Main -->
    <div class="stg-main">
        <div id="stg-content-area" class="stg-card"></div>
    </div>

</div>


<script>

function stgLoadPage(url, title){
    fetch(url)
    .then(res => res.text())
    .then(data => {
        document.getElementById("stg-content-area").innerHTML =
            `<div class="stg-card-header">
                <div class="stg-card-title">${title}</div>
             </div>` + data;
    });
}

document.querySelectorAll(".stg-menu-item").forEach(item => {

    item.addEventListener("click", function(){

        document.querySelectorAll(".stg-menu-item")
        .forEach(i => i.classList.remove("stg-active"));

        this.classList.add("stg-active");

        stgLoadPage(this.dataset.url, this.innerText);

    });

});

stgLoadPage("{{ route('settings.general') }}","{{ __('messages.general_settings') }}");

</script>

</x-master-layout>
