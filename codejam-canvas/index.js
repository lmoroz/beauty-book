window.addEventListener('DOMContentLoaded', () => {

    const error_element = document.getElementById('error-message');
    error_element.addEventListener('click', () => error_element.classList.remove('mdc-snackbar--open'));

    const hex_to_rgba = color => {
        // transform hex representation of color to rgba
        color = color.match(/(..)/g).map(digit => parseInt(digit, 16));
        color.push(255); // add 100% opacity
        return color;
    };

    class CanvasClass {
        constructor(selector) {
            this.base_size = 512;
            this.datasets = {};
            this.element = document.querySelector(selector);
            this.canvas_ctx = this.element.getContext('2d');
        }

        draw_data = function(data) {
            error_element.classList.remove('mdc-snackbar--open'); //close error message if opened

            this.canvas_ctx.clearRect(0, 0, this.element.width, this.element.height); // clear canvas
            this.element.width = data.size; // set canvas size to data size
            this.element.height = data.size;

            const imageData = this.canvas_ctx.getImageData(0, 0, data.size, data.size);
            imageData.data.set(data.pixels);
            this.canvas_ctx.putImageData(imageData, 0, 0);

        };

        draw_image = function(img) {
            error_element.classList.remove('mdc-snackbar--open');

            this.canvas_ctx.clearRect(0, 0, this.element.width, this.element.height);
            this.element.width = img.width;
            this.element.height = img.height;
            this.canvas_ctx.drawImage(img, 0, 0);
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
                                  ? data.flat()
                                  : data.flat().map(hex_to_rgba),
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

    const codejam_canvas = new CanvasClass('#codejam-canvas');

    const show_error = (message = false) => {
        if (message) console.error(message);
        error_element.classList.add('mdc-snackbar--open');
    };

    document.getElementById('switch-menu').
        addEventListener('change', function(event) {
            const target = event.target;
            if (target.tagName === 'INPUT' && target.type === 'radio' && target.checked) {
                if (target.dataset['type'] === 'json') codejam_canvas.load_json(target);
                if (target.dataset['type'] === 'image') codejam_canvas.load_image(target);

            }
        });
});
