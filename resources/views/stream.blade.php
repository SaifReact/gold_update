<!DOCTYPE html>
<html>
<body>

<h2>SSE Test</h2>
<pre id="output">Connecting...</pre>

<script>
const output = document.getElementById('output');
const source = new EventSource('/test-sse');

source.onmessage = (event) => {
    output.textContent = event.data;
};

source.onerror = () => {
    output.textContent = 'ERROR: SSE connection failed';
};
</script>

</body>
</html>
