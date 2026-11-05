</main>
<footer class="footer mt-5 py-4">
    <div class="container d-flex flex-wrap justify-content-between gap-2"><span>Institut Universitaire de Technologie de Douala</span><span>Service de consultation et de suivi des diplômes</span></div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= rtrim(\App\Core\Env::get('APP_URL', ''), '/') ?>/assets/js/app.js?v=<?= (int)@filemtime(dirname(__DIR__, 3) . '/public/assets/js/app.js') ?>"></script>
</body>

</html>