Array.prototype.pluck = function(col = undefined) {
  if (!col) {
    return this;
  } else {
    return this.map(item => {
      let value = item;
      for (const key of col.split('.')) {
        value = value ? value[key] : undefined;
      }
      return value;
    });
  }
};

Array.prototype.sum = function(col = undefined) {
  return this.pluck(col).reduce((acc, curr) => acc + (curr ?? 0), 0);
};

Array.prototype.avg = function(col = undefined) {
  return this.length === 0 ? 0 : this.sum(col) / this.length;
};

Number.prototype.fraction = function(fraction = 0) {
  return new Intl.NumberFormat('de-DE', {minimumFractionDigits: fraction, maximumFractionDigits: fraction}).format(this);
};

String.prototype.fraction = function(fraction = 0) {
  return parseFloat(this).fraction(fraction);
};
