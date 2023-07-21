<?php if (!$isLoggedIn) : ?>
    <div class="googleButtonContainer">
        <a class="googleButton" href="loginWithGoogle">Google İle Giriş Yap</a>
    </div>
<?php endif; ?>
<?php if ($isLoggedIn) : ?>
    <div class="d-flex row justify-content-between align-items-center">
        <h2>Google Search Console</h2>
        <div>
            <?php echo '<a class="btn btn-primary" href="scanUrls' . '?code=' . $authorizationCode . '">'; ?>
            Tüm Linkleri Tara
            </a>
            <?php echo '<a class="btn btn-success" href="analyzeKeywordsWeekly">'; ?>
            Haftalık Kelime Analiz Sonucu
            </a>
            <?php echo '<a class="btn btn-warning" href="analyzeKeywords">'; ?>
            Kelime Analiz Et
            </a>
        </div>
    </div>
    <br>
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col">Linkler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($apiResponse as $data) : ?>
                <tr>
                    <th scope="row">
                        <?php echo '<a href="urlResults' . '?code=' . $authorizationCode . '&link=' . $data["link"] . '">'; ?>
                        <?php echo $data["link"] ?>
                        </a>
                    </th>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>