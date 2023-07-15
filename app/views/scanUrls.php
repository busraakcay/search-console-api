<div class="container">
    <?php foreach ($inspectedURLs as $inspectedURL) : ?>
        <div class="container">
            <h4 class="pb-2">Index Durumu <small>[<?php echo $inspectedURL["indexStatusResult"]["verdict"]; ?>]</small></h4>
            <small><b><?php echo $inspectedURL["link"]; ?></b></small>
            <br><br>
            <p><b>URL Durumu: </b><?php echo $inspectedURL["indexStatusResult"]["coverageState"]; ?> </p>
            <p><b>Index Analizi: </b><?php echo getVerdictDescription($inspectedURL["indexStatusResult"]["verdict"]); ?> </p>
            <p><b>Son Taranma Tarihi: </b><?php echo formatDateString($inspectedURL["indexStatusResult"]["lastCrawlTime"]); ?></p>
            <p><b>Index Durumu: </b><?php echo getIndexingStateDescription($inspectedURL["indexStatusResult"]["indexingState"]); ?> </p>
            <p><b>Sayfa Getirme Durumu: </b><?php echo getPageFetchStateDescription($inspectedURL["indexStatusResult"]["pageFetchState"]); ?> </p>
            <p><b>Robots.txt Kural Durumu: </b><?php echo getRobotTxtStateDescription($inspectedURL["indexStatusResult"]["robotsTxtState"]); ?> </p>
            <p>Makinecim.com'da ilanı görmek için <a href="<?php echo $inspectedURL["link"] ?>">tıklayınız</a></p>
            <p>Bu URL'i search console'da denetlemek için <a href="<?php echo $inspectedURL["searchConsoleLink"] ?>">tıklayınız</a></p>
            <?php if (isset($inspectedURL["mobileUsabilityResult"])) : ?>
                <br>
                <h4 class="pb-2">Mobil Kullanılabilirlik <small>[<?php echo $inspectedURL["mobileUsabilityResult"]["verdict"]; ?>]</small></h4>
                <?php foreach ($inspectedURL["mobileUsabilityResult"]["issues"] as $key => $issue) : ?>
                    <p><b>[Sorun <?php echo $key + 1 ?>]</b> <?php echo $issue["message"] ?> [<?php echo $issue["issueType"] ?>] </p>
                <?php endforeach; ?>

            <?php endif; ?>
            <?php if (isset($inspectedURL["richResultsResult"])) : ?>
                <br>
                <h4 class="pb-2">Zengin Sonuçlar <small>[<?php echo $inspectedURL["richResultsResult"]["verdict"]; ?>]</small> </h4>
                <?php foreach ($inspectedURL["richResultsResult"]["detectedItems"] as $key => $detectedItems) : ?>
                    <p><b>[Sorun <?php echo $key + 1  ?>] </b><?php echo $detectedItems["richResultType"] ?></p>
                    <ul class="ml-2">
                        <?php foreach ($detectedItems["items"] as $key => $item) : ?>
                            <li><b>[Öğe <?php echo $key + 1  ?>] </b><?php echo $item["name"] ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <hr>
    <?php endforeach; ?>
</div>