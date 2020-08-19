var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/**
 * Author: Alexander Flores
 * Date: 14/09/2017
 * Version: 0.1
 */

var xls_load = function () {
    function xls_load(arrWidget, draWidget) {
        _classCallCheck(this, xls_load);

        this.op = 'd019c0ac-9cb9-11e7-93c0-286ed488ca86';
        this.aw = arrWidget;
        this.dw = draWidget;
        this.boolProcess = false;
        this.mr = 500;
        this.arrOk = this.validateArray();
        this.pluginOk();

        this.objXHR = null;
    }

    _createClass(xls_load, [{
        key: 'validateArray',
        value: function validateArray() {
            if (this.aw.length > 0) {
                var o = true;
                for (var i = 0; i < this.aw.length; i++) {
                    if (this.aw[i].elementID === "") o = false;else if ($('#' + this.aw[i].elementID).length !== 1) o = false;
                    if (typeof this.aw[i].validate === 'undefined') o = false;else if (this.aw[i].validate === '') o = false;
                    if (typeof this.aw[i].process === 'undefined') o = false;else if (this.aw[i].process === "") o = false;
                }
                return o;
            }
            return false;
        }
    }, {
        key: 'pluginOk',
        value: function pluginOk() {
            if (!this.arrOk) {
                this.badConfig();
                return this.arrOk;
            }
            return this.arrOk;
        }
    }, {
        key: 'badConfig',
        value: function badConfig() {
            this.dw.alertDialog("Mala configuración de plugin");
        }
    }, {
        key: 'drawInputFile',
        value: function drawInputFile(k) {
            var _this = this;

            var content = $('#' + this.aw[k].elementID);
            content.html('');
            var form = $('<form></form>').attr({
                'class': '',
                'enctype': 'multipart/form-data'
            });content.append(form);

            var divFile = $('<div></div>').addClass("form-group");form.append(divFile);
            var input = $('<input />').attr({
                'type': 'file',
                'name': 'iXload',
                'id': 'file-xls-' + k,
                'class': 'inputfile-xls',
                'accept': 'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            });divFile.append(input);

            var labelInput = $('<label></label>').attr({
                'for': 'file-xls-' + k
            });divFile.append(labelInput);
            var strongInput = $('<strong></strong>');labelInput.append(strongInput);
            var iInput = $('<i></i>').attr({
                'class': 'fa fa-cloud-upload',
                'aria-hidden': 'true'
            });strongInput.append(iInput);
            strongInput.append('&nbsp;&nbsp;Seleccionar archivo&hellip;');
            var spanInput = $('<span>Ningún archivo seleccionado</span>');labelInput.append(spanInput);
            input.on('change', function (e) {
                if (_this.boolProcess) {
                    _this.dw.alertDialog('No se pueden procesar 2 archivos simultaneamente');
                    return false;
                }
                var fileName = e.target.value.split('\\').pop();
                if (fileName) {
                    spanInput.html(fileName);
                    form.submit();
                } else spanInput.html('Ningún archivo seleccionado');
            });

            var cntMessages = $('<div></div>').addClass('xls-msg');content.append(cntMessages);

            var divProgress = $('<div></div>').addClass('progress hide');cntMessages.append(divProgress);
            var divInsideProgress = $('<div></div>').attr({
                'class': 'progress-bar',
                'role': 'progressbar',
                'aria-valuenov': '0',
                'aria-valuemin': '0',
                'aria-valuemax': '100'
            }).css('width', '0%');divProgress.append(divInsideProgress);

            var divStep = $('<div></div>').addClass('xls-step');cntMessages.append(divStep);

            form.on('submit', function (e) {
                e.preventDefault();
                //const frmData = new FormData(form);
                $.ajax({
                    type: 'POST',
                    url: _this.constructor.getToken(_this.op) + '&opt=save',
                    data: new FormData(form[0]),
                    dataType: 'JSON',
                    contentType: false,
                    cache: false,
                    processData: false,
                    beforeSend: function beforeSend() {
                        _this.dw.openLoading();
                    },
                    success: function success(d) {
                        _this.dw.closeLoading();
                        if (d.valido === 1) {
                            _this.aw[k].r = d;
                            _this.aw[k].r.aw = k;
                            _this.aw[k].r.file = spanInput.html();
                            _this.processResponse(k, _this.aw[k].r);
                        } else {
                            _this.dw.alertDialog(d.msj);
                        }
                    },
                    error: function error() {
                        _this.dw.closeLoading();
                    }
                });
            });
        }
    }, {
        key: 'drawWidget',
        value: function drawWidget() {
            if (!this.pluginOk()) return false;
            for (var i = 0; i < this.aw.length; i++) {
                this.drawInputFile(i);
            }
        }
    }, {
        key: 'processResponse',
        value: function processResponse(k) {
            if (this.aw[k].column.length > 0) this.validateColumn(k);
            if (typeof this.aw[k].run !== 'undefined') {
                this.aw[k].run(this.aw[k].r);
            } else {
                this.processXls(k);
            }
        }
    }, {
        key: 'validateColumn',
        value: function validateColumn(k) {
            var _this2 = this;

            $.each(this.aw[k].r.sheets, function (key, val) {
                _this2.aw[k].r.sheets[key].columnOk = false;
                if (typeof _this2.aw[k].column[key] !== 'undefined') {
                    var o = true;
                    if (val.headers.length > 0) {
                        $.each(val.headers, function (c, d) {
                            if (typeof _this2.aw[k].column[key][c] === 'undefined' || _this2.aw[k].column[key][c] !== d) {
                                o = false;
                                return false;
                            }
                        });
                    } else {
                        o = false;
                    }
                    _this2.aw[k].r.sheets[key].columnOk = o;
                }
            });
        }
    }, {
        key: 'processXls',
        value: function processXls(k, p) {
            var _this3 = this;

            if (Object.keys(p).length > 0) this.saveParams(k, p);

            var arrSteps = [];
            if (typeof this.aw[k] !== 'undefined') {
                var content = $('#' + this.aw[k].elementID);
                if (content.length > 0) {
                    var progress = content.find('.xls-msg').find('.progress');
                    progress.removeClass('hide');

                    var dStep = content.find('.xls-step');

                    var btnValidate = $('<button>Validar datos</button>').attr({
                        'class': 'btn btn-default pro-validate'
                    });dStep.append(btnValidate);

                    var btnSave = $('<button>Guardar datos</button>').attr({
                        'class': 'btn btn-default pro-save'
                    });dStep.append(btnSave);

                    $.each(this.aw[k].r.sheets, function (s, d) {
                        if (d.columnOk) {
                            var steps = _this3.calculateSteps(d.rows);
                            var init = 2;
                            var sheet = 0;
                            var process = 0;
                            for (var ss in steps) {
                                if (s !== sheet) {
                                    init = 2;
                                    process = steps[ss] + 1;
                                    sheet = s;
                                } else {
                                    init = process;
                                    if (steps[ss] < _this3.mr) {
                                        process = process + steps[ss];
                                    } else {
                                        process += _this3.mr;
                                    }
                                }

                                var divStep = $('<div>Hoja ' + s + ' - procesando de ' + init + ' a ' + process + ' filas</div>').addClass('col-lg-12');dStep.append(divStep);

                                var btnData = $('<button>Obteniendo datos</button>').attr({
                                    'class': 'btn btn-default pro-data'
                                });divStep.append(btnData);

                                arrSteps.push({
                                    sheet: s,
                                    rows: steps[ss],
                                    obj: divStep
                                });
                            }
                        }
                    });
                    if (arrSteps.length > 0) {
                        this.nextLoad(k, arrSteps, 0);
                    }
                }
            }
        }
    }, {
        key: 'nextLoad',
        value: function nextLoad(k, arrSteps, step) {
            var _this4 = this;

            if (typeof arrSteps[step] !== 'undefined') {
                this.boolProcess = true;

                var i = $('<i><i>').attr({
                    'class': 'fa fa-spinner',
                    'aria-hidden': 'true'
                });
                arrSteps[step].obj.find('.pro-data').append(i);

                if (this.objXHR !== null) this.objXHR = null;

                this.objXHR = $.ajax({
                    type: 'GET',
                    url: this.constructor.getToken(this.op) + '&opt=getData',
                    data: {
                        load: this.aw[k].r.load_id,
                        sheet: arrSteps[step].sheet,
                        rows: arrSteps[step].rows
                    },
                    cache: false,
                    dataType: 'json',
                    beforeSend: function beforeSend() {},
                    success: function success(d) {
                        arrSteps[step].obj.find('.pro-data').find('i').remove();
                        if (d.valido === 1) {
                            var _i = $('<i><i>').attr({
                                'class': 'fa fa-check-circle',
                                'aria-hidden': 'true'
                            });
                            arrSteps[step].obj.find('.pro-data').append(_i);
                        } else {
                            var _i2 = $('<i><i>').attr({
                                'class': 'fa fa-times-circle',
                                'aria-hidden': 'true'
                            });
                            arrSteps[step].obj.find('.pro-data').append(_i2);
                        }
                        _this4.updateProgressBar(k, arrSteps.length, step + 1);
                        _this4.nextLoad(k, arrSteps, step + 1);
                    },
                    error: function error() {
                        _this4.boolProcess = false;
                        var i = $('<i><i>').attr({
                            'class': 'fa fa-times-circle',
                            'aria-hidden': 'true'
                        });
                        arrSteps[step].obj.find('.pro-data').append(i);
                        _this4.dw.alertDialog("Hubo un problema al obtener datos");
                    }
                });
            } else {
                this.boolProcess = false;
                this.validateData(k, arrSteps.length, step + 1);
            }
        }
    }, {
        key: 'validateData',
        value: function validateData(k, coutnSteps, step) {
            var _this5 = this;

            this.boolProcess = true;
            var content = $('#' + this.aw[k].elementID);
            var dStep = content.find('.xls-step');

            var i = $('<i><i>').attr({
                'class': 'fa fa-spinner',
                'aria-hidden': 'true'
            });
            dStep.find('.pro-validate').append(i);

            if (this.objXHR !== null) this.objXHR = null;
            this.objXHR = $.ajax({
                type: 'GET',
                url: this.constructor.getToken(this.op) + '&opt=validateData',
                data: {
                    load: this.aw[k].r.load_id,
                    validate: this.aw[k].validate
                },
                cache: false,
                dataType: 'json',
                beforeSend: function beforeSend() {},
                success: function success(d) {
                    dStep.find('.pro-validate').find('i').remove();
                    if (d.valido === 1) {
                        var _i3 = $('<i><i>').attr({
                            'class': 'fa fa-check-circle',
                            'aria-hidden': 'true'
                        });
                        dStep.find('.pro-validate').append(_i3);
                    } else {
                        var _i4 = $('<i><i>').attr({
                            'class': 'fa fa-times-circle',
                            'aria-hidden': 'true'
                        });
                        dStep.find('.pro-validate').append(_i4);
                    }
                    _this5.updateProgressBar(k, coutnSteps, step);
                    _this5.bulk_data(k, coutnSteps, step + 1);
                },
                error: function error() {
                    _this5.boolProcess = false;
                    var i = $('<i><i>').attr({
                        'class': 'fa fa-times-circle',
                        'aria-hidden': 'true'
                    });
                    dStep.find('.pro-validate').append(i);
                    _this5.dw.alertDialog("Hubo un problema al validar datos");
                }
            });
        }
    }, {
        key: 'bulk_data',
        value: function bulk_data(k, coutnSteps, step) {
            var _this6 = this;

            this.boolProcess = true;
            var content = $('#' + this.aw[k].elementID);
            var dStep = content.find('.xls-step');

            var i = $('<i><i>').attr({
                'class': 'fa fa-spinner',
                'aria-hidden': 'true'
            });
            dStep.find('.pro-save').append(i);

            if (this.objXHR !== null) this.objXHR = null;
            this.objXHR = $.ajax({
                type: 'GET',
                url: this.constructor.getToken(this.op) + '&opt=process',
                data: {
                    load: this.aw[k].r.load_id,
                    process: this.aw[k].process
                },
                cache: false,
                dataType: 'json',
                beforeSend: function beforeSend() {},
                success: function success(d) {
                    dStep.find('.pro-save').find('i').remove();
                    if (d.valido === 1) {
                        var _i5 = $('<i><i>').attr({
                            'class': 'fa fa-check-circle',
                            'aria-hidden': 'true'
                        });
                        dStep.find('.pro-save').append(_i5);
                    } else {
                        var _i6 = $('<i><i>').attr({
                            'class': 'fa fa-times-circle',
                            'aria-hidden': 'true'
                        });
                        dStep.find('.pro-save').append(_i6);
                    }
                    _this6.updateProgressBar(k, coutnSteps, step);
                    _this6.dw.alertDialog(d.msj);
                },
                error: function error() {
                    _this6.boolProcess = false;
                    var i = $('<i><i>').attr({
                        'class': 'fa fa-times-circle',
                        'aria-hidden': 'true'
                    });
                    dStep.find('.pro-save').append(i);
                    _this6.dw.alertDialog("Hubo un problema al procesar datos");
                }
            });
        }
    }, {
        key: 'updateProgressBar',
        value: function updateProgressBar(k, c, step) {
            var p = 100 / (c + 2);
            var s = Math.round(p);
            if (p > s) s++;
            s = s * (step * 1);
            if (s > 100) s = 100;
            var content = $('#' + this.aw[k].elementID);
            var progress = content.find('.xls-msg').find('.progress');
            var bar = progress.find('.progress-bar');
            bar.attr({
                'aria-valuenov': s
            }).css({
                'width': s + '%'
            });
        }
    }, {
        key: 'calculateSteps',
        value: function calculateSteps(r) {
            var s = [];
            while (r > 0) {
                r -= this.mr;
                if (r <= 0) s.push(r + this.mr);else s.push(this.mr);
            }
            return s;
        }
    }, {
        key: 'deletexls',
        value: function deletexls(k) {
            var _this7 = this;

            if (typeof this.aw[k] !== 'undefined') {
                if (this.aw[k].r.load_id !== 'undefined') {
                    $.ajax({
                        type: 'POST',
                        url: this.constructor.getToken(this.op) + '&opt=delete&load=' + this.aw[k].r.load_id,
                        beforeSend: function beforeSend() {
                            _this7.dw.openLoading();
                        },
                        success: function success(d) {
                            _this7.dw.closeLoading();
                            if (d.valido === 1) _this7.drawInputFile(k);
                            _this7.dw.alertDialog(d.msj);
                        },
                        error: function error() {
                            _this7.dw.closeLoading();
                        }
                    });
                }
            }
        }
    }, {
        key: 'saveParams',
        value: function saveParams(k, p) {
            var _this8 = this;

            if (this.objXHR !== null) this.objXHR = null;
            this.objXHR = $.ajax({
                type: 'GET',
                url: this.constructor.getToken(this.op) + '&opt=extra&load=' + this.aw[k].r.load_id,
                data: p,
                dataType: 'json',
                beforeSend: function beforeSend() {},
                success: function success(d) {
                    if (d.valido === 0) _this8.dw.alertDialog(d.msj);
                },
                error: function error() {
                    _this8.dw.alertDialog("Hubo un problema al guardar parametros extras");
                }
            });
        }
    }], [{
        key: 'getToken',
        value: function getToken(o, f, m) {
            if (!o) {
                alert("Se necesita codigo de operacion");
                return false;
            }
            if (!f) f = "json";
            if (!m) m = "w";

            return "webservice.php?o=" + o + "&f=" + f + "&m=" + m;
        }
    }]);

    return xls_load;
}();