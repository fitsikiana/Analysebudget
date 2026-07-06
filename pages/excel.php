<table class="table-moderne">
    <thead>
        <tr>
            <th>Rubrique</th>
            <th>Budget Initial</th>
            <th>Dépense Mois</th>
            <th>Solde Restant</th>
            <th>État (Taux)</th>
        </tr>
    </thead>
    <tbody>
        
            <tr>
                <td><?php echo $data['rubrique']; ?></td>
                <td><?php echo $data['initial']; ?> €</td>
                <td style="color: red;"> €</td>
                <td><strong> €</strong></td>
                <td>
                    <?php 
                        $classe = 'vert';
                        if ($data['taux'] >= 100) $classe = 'rouge';
                        elseif ($data['taux'] >= 80) $classe = 'orange';
                    ?>
                    <span class="badge <?php echo $classe; ?>">
                        <?php echo round($data['taux'], 2); ?> %
                    </span>
                </td>
            </tr>
  
    </tbody>
</table>