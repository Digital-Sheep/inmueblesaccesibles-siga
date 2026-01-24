<script>
    window.addEventListener('open-url-new-tab', event => {
        window.open(event.detail.url, '_blank');
        window.location.reload();
    });
</script>
