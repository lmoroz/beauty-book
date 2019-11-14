window.addEventListener('DOMContentLoaded', () => {

    const errorElement = document.getElementById('error-message');
    errorElement.addEventListener('click', () => errorElement.classList.remove('mdc-snackbar--open'));

    const hexToRgba = color => {
        // transform hex representation of color to rgba
        color = color.match(/(..)/g).map(digit => parseInt(digit, 16));
        color.push(1); // add 100% opacity
        return `rgba(${ color.join(',')})`;
    };
    const rgbaArrayToRgba = color => {
        color[3] = color[3] / 255; // convert wrong aplha
        return `rgba(${ color.join(',') })`;
    };

    class CanvasClass {
        constructor(selector) {
            this.baseSize = 512;
            this.datasets = {};
            this.element = document.querySelector(selector);
            this.canvas_ctx = this.element.getContext('2d');
            this.element.width = this.baseSize;
            this.element.height = this.baseSize;
        }

        draw_data = function(data) {
            errorElement.classList.remove('mdc-snackbar--open'); //close error message if opened

            this.canvas_ctx.clearRect(0, 0, this.element.width, this.element.height); // clear canvas
            const imageData = this.canvas_ctx.getImageData(0, 0, data.size, data.size);
            const dataMultiply = this.element.width/ data.size;


            data.pixels.forEach((dataRow,rIndex) => {
                dataRow.forEach((dataCol,colIndex) => {
                    this.canvas_ctx.fillStyle = dataCol;
                    this.canvas_ctx.fillRect(rIndex* dataMultiply, colIndex* dataMultiply, dataMultiply, dataMultiply);
                })
            });
        };

        draw_image = function(img) {
            errorElement.classList.remove('mdc-snackbar--open');

            this.canvas_ctx.clearRect(0, 0, this.element.width, this.element.height);
            this.canvas_ctx.drawImage(img, 0, 0, this.baseSize, this.baseSize);
        };

        load_json = function(source) {
            const self = this;
            const data = self.datasets[source.value];
            if (!data) {
                /*
                 If data wasn't loaded yet - download it and convert it into a flat array
                 */
                fetch(source.value, {cache: 'force-cache'}).
                    then(res => res.json()).
                    then(data => ({
                        'size': data.length,
                        'pixels': Array.isArray(data[0][0])
                                  ? data.map(dataRow => dataRow.map(dataColor => rgbaArrayToRgba(dataColor)))
                                  : data.map(dataRow => dataRow.map(dataColor => hexToRgba(dataColor))),
                    })).
                    then(data => {
                        self.datasets[source.value] = data; // save data for further usage
                        self.draw_data(data);
                    }).
                    catch(error => show_error(error));
            } else self.draw_data(data);
        };

        load_image = function(source) {
            const self = this;
            const img = self.datasets[source.value];
            if (!img) {
                const img = new Image();
                img.onload = function() {
                    self.datasets[source.value] = img;
                    self.draw_image(img);
                };
                img.onerror = () => show_error();
                img.src = source.value;
            } else self.draw_image(img);
        };
    }

    const codejamCanvas = new CanvasClass('#codejam-canvas');

    const show_error = (message = false) => {
        if (message) console.error(message);
        errorElement.classList.add('mdc-snackbar--open');
    };

    document.getElementById('switch-menu').
        addEventListener('change', function(event) {
            const target = event.target;
            if (target.tagName === 'INPUT' && target.type === 'radio' && target.checked) {
                if (target.dataset['type'] === 'json') codejamCanvas.load_json(target);
                if (target.dataset['type'] === 'image') codejamCanvas.load_image(target);

            }
        });
});
