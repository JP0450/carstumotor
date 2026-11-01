<datalist id="marcas-options">
    @foreach ($marcas as $marca)
        <option value="{{ $marca }}">
    @endforeach
</datalist>
<datalist id="modelos-options">
    @foreach ($modelos as $modelo)
        <option value="{{ $modelo }}">
    @endforeach
</datalist>
<datalist id="colores-options">
    @foreach ($colores as $color)
        <option value="{{ $color }}">
    @endforeach
</datalist>
<script>
    const modelosPorMarca = @json($modelosPorMarca);
    const marcaInput = document.getElementById('marca');
    const modeloInput = document.getElementById('modelo');
    const modelosList = document.getElementById('modelos-options');
    const modelosExistentes = Array.from(modelosList.options, (option) => option.value);

    const actualizarModelos = () => {
        const marca = marcaInput.value.trim().toLocaleLowerCase();
        const sugerencias = marca ? (modelosPorMarca[marca] || []) : modelosExistentes;

        modelosList.replaceChildren(...sugerencias.map((modelo) => {
            const option = document.createElement('option');
            option.value = modelo;
            return option;
        }));
    };

    marcaInput.addEventListener('input', actualizarModelos);
    actualizarModelos();
</script>
