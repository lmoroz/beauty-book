window.addEventListener('DOMContentLoaded', () => {

    const error_element = document.getElementById('error-message');
    error_element.addEventListener('click', () => error_element.classList.remove('mdc-snackbar--open'));

    class CanvasClass {
        constructor(selector) {
            this.base_size = 512;
            this.datasets = {};
            this.element = document.querySelector(selector);
            this.canvas_ctx = this.element.getContext('2d');
        }

        draw_data = function(data) {
            const cell_size = this.base_size / data.length;
            const self = this;
            data.forEach((row, r_i) =>
                row.forEach((fill_color, c_i) => {
                    const [cell_row_start, cell_col_start] = [cell_size * r_i, cell_size * c_i];
                    self.canvas_ctx.fillStyle = Array.isArray(fill_color)
                                                ? `rgba(${ fill_color.join(',') })`
                                                : '#' + fill_color;
                    self.canvas_ctx.fillRect(cell_row_start, cell_col_start, cell_size, cell_size);
                }),
            );
        };

        draw_image = function(img) {
            this.canvas_ctx.drawImage(img, 0, 0, this.base_size, this.base_size);
        };

        load_json = function(source) {
            this.canvas_ctx.clearRect(0, 0, this.base_size, this.base_size);
            const self = this;
            const data = self.datasets[source.value];
            if (!data) {
                fetch(source.value, {cache: 'force-cache'}).then(res => res.json()).then(data => {
                    self.datasets[source.value] = data;
                    self.draw_data(data);
                }).catch(error => show_error(error));
            } else self.draw_data(data);
        };

        load_image = function(source) {
            this.canvas_ctx.clearRect(0, 0, this.base_size, this.base_size);
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
        window.setTimeout(() => error_element.classList.remove('mdc-snackbar--open'), 5000);
    };

    document.getElementById('switch-menu').addEventListener('change', function(event) {
        const target = event.target;
        if (target.tagName === 'INPUT' && target.type === 'radio' && target.checked) {
            if (target.dataset['type'] === 'json') codejam_canvas.load_json(target);
            if (target.dataset['type'] === 'image') codejam_canvas.load_image(target);

        }
    });
});
