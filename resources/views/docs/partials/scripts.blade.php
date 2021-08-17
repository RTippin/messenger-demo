@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.1.0/styles/monokai.min.css">
@endpush
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.1.0/highlight.min.js"></script>
@endpush
@push('special-js')
    <script>
        document.querySelectorAll('#readmeContent img').forEach((x) => {
            if(x.src === 'https://raw.githubusercontent.com/RTippin/messenger/1.x/docs/images/image1.png?raw=true'){
                x.classList.add("img-fluid");
            }
        })
        document.querySelectorAll('code:not(.hljs)').forEach((block) => {
            hljs.highlightElement(block)
        });
    </script>
@endpush
