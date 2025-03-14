<script>
  Object.defineProperty(Object.prototype, 'xxVal', {
    value: function() {
      return Object.values(this);
    },
    enumerable: false,
  });

  Object.defineProperty(Object.prototype, 'xxKey', {
    value: function() {
      return Object.keys(this);
    },
    enumerable: false,
  });

  Object.defineProperty(Object.prototype, 'xxEnt', {
    value: function() {
      return Object.entries(this);
    },
    enumerable: false,
  });

  Object.defineProperty(Array.prototype, 'xxPluck', {
    value: function(col = undefined) {
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
    },
    enumerable: false,
  });

  Object.defineProperty(Array.prototype, 'xxSum', {
    value: function(col = undefined) {
      return this.xxPluck(col).reduce((acc, curr) => acc + (curr ?? 0), 0);
    },
    enumerable: false,
  });

  Object.defineProperty(Array.prototype, 'xxAvg', {
    value: function(col = undefined) {
      return this.length === 0 ? 0 : this.xxSum(col) / this.length;
    },
    enumerable: false,
  });

  Object.defineProperty(Number.prototype, 'xxFraction', {
    value: function(fraction = 0) {
      return new Intl.NumberFormat('de-DE', {minimumFractionDigits: fraction, maximumFractionDigits: fraction}).format(this);
    },
    enumerable: false,
  });

  Object.defineProperty(String.prototype, 'xxFraction', {
    value: function(fraction = 0) {
      return parseFloat(this).xxFraction(fraction);
    },
    enumerable: false,
  });

  Object.defineProperty(String.prototype, 'xxToDateFormat', {
    value: function() {
      const date = new Date(this);
      if (isNaN(date.getTime())) {
        throw new Error('Invalid date format');
      }
      return date
      .toLocaleDateString(
        'id-ID',
        {
          day: '2-digit',
          month: 'short',
          year: '2-digit',
        },
      )
      .replace(/ /g, '-');
    },
    enumerable: false,
  });

  Object.defineProperty(String.prototype, 'xxToEpoch', {
    value: function() {
      const date = new Date(this);
      if (isNaN(date.getTime())) {
        throw new Error('Invalid date format');
      }
      return date.getTime();
    },
    enumerable: false,
  });
</script>
