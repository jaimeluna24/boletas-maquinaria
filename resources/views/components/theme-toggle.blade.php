<button
    x-data
    @click="
        const nuevoTema = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', nuevoTema);
        localStorage.setItem('theme', nuevoTema);
    "
    type="button"
    class="theme-toggle-btn"
>
    <span><svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 0 16 16">
  <path d="M0 0h16v16H0z" fill="none" />
  <path fill="currentColor" d="M8 1.002a7 7 0 1 0 0 14a7 7 0 0 0 0-14m0 13v-12a6 6 0 1 1 0 12" />
</svg></span>
</button>
