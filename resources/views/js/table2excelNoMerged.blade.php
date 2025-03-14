<script>
    $.fn.table2excelNoMerged = function(props = {}) {
        $('<table>')
            .html(
                $('<tbody>')
                .append(
                    this
                    .find('tr')
                    .get()
                    .reduce((acc, row) => {
                        const pop = acc[acc.length - 1] ?? [];

                        let raw = $(row)
                            .find('th, td')
                            .get()
                            .reduce((subAcc, {
                                colSpan,
                                rowSpan,
                                innerText
                            }) => {
                                for (let span = 0; span < colSpan; span++) {
                                    subAcc.push({
                                        rowSpan,
                                        innerText: span === 0 ? innerText : ''
                                    });
                                }
                                return subAcc;
                            }, []);

                        const [fix] = [pop, raw].sort((a, b) => b.length - a.length);

                        for (let i = 0; i < fix.length; i++) {
                            let rowSpan = pop[i]?.rowSpan ?? 1;
                            if (rowSpan > 1) {
                                rowSpan--;
                                raw.unshift({
                                    rowSpan,
                                    innerText: ''
                                });
                            }
                        }

                        return [...acc, raw];
                    }, [])
                    .map(row =>
                        $('<tr>')
                        .append(
                            row.map(col =>
                                $('<td>').text(col.innerText)
                            )
                        )
                    )
                ),
            )
            .table2excel(props);
        return this;
    };
</script>
