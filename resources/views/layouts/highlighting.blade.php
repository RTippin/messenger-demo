@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.1.0/styles/github-dark.min.css">
    <style>
        code:not(.hljs) {
            background: #20211c !important;
            color: #ddd !important;
            padding: 3px 5px;
        }
    </style>
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
        document.querySelectorAll('code[class^=language]:not(.hljs)').forEach((block) => {
            hljs.highlightElement(block)
        });
    </script>
@endpush
