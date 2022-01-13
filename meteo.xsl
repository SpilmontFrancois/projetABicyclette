<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="UTF-8" indent="yes" />
    <xsl:strip-space elements="*" />

    <!-- On démarre de la racine -->
    <xsl:template match="/">
        <html lang="fr">
            <head>
                <title>A Bicyclette</title>
            </head>
            <body>
                <div id="conditions">
                    <h1 class="ms-2">Projet A Bicyclette</h1>
                    <h2 class="ms-4">Conditions météo</h2>
                    <xsl:apply-templates />
                    <hr/>
                </div>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="previsions">
        <xsl:apply-templates select="echeance[1]" />
    </xsl:template>

    <xsl:template match="echeance[1]">
        <table class="table table-bordered ms-5 w-75">
            <thead>
                <tr>
                    <th>Température</th>
                    <th>Vent Moyen</th>
                    <th>Pluie</th>
                    <th>Humidité</th>
                    <th>Risque de neige</th>
                    <th>Condition</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>
                        <xsl:choose>
                            <xsl:when test="format-number(temperature/level[2] - 273.15, '.00') &lt; 5">
                                <img src="./assets/icons/froid.png" alt="temperature" />
                            </xsl:when>
                            <xsl:when test="(format-number(temperature/level[2] - 273.15, '.00') &gt; 5) and (format-number(temperature/level[2] - 273.15, '.00') &lt; 20)">
                                <img src="./assets/icons/normal.png" alt="temperature" />
                            </xsl:when>
                            <xsl:when test="format-number(temperature/level[2] - 273.15, '.00') &gt; 20">
                                <img src="./assets/icons/chaud.png" alt="temperature" />
                            </xsl:when>
                        </xsl:choose>
                        <br />
                        <xsl:value-of select="format-number(temperature/level[2] - 273.15, '.00')" />
                        <xsl:text> °C</xsl:text>
                    </td>
                    <td>
                        <xsl:choose>
                            <xsl:when test="(vent_moyen/level) &lt; 20">
                                <img src="./assets/icons/leger.png" alt="vent" />
                            </xsl:when>
                            <xsl:when test="(vent_moyen/level) &lt; 50">
                                <img src="./assets/icons/pas_fort.png" alt="vent" />
                            </xsl:when>
                            <xsl:when test="(vent_moyen/level) &lt; 75">
                                <img src="./assets/icons/fort.png" alt="vent" />
                            </xsl:when>
                            <xsl:when test="(vent_moyen/level) &gt; 74">
                                <img src="./assets/icons/tempete.png" alt="vent" />
                            </xsl:when>
                        </xsl:choose>
                        <br />
                        <xsl:value-of select="vent_moyen/level" />
                        <xsl:text> km/h</xsl:text>
                    </td>
                    <td>
                        <xsl:choose>
                            <xsl:when test="pluie &lt; 11">
                                <img src="./assets/icons/pas_de_neige.png" alt="pluie" />
                            </xsl:when>
                            <xsl:when test="pluie &lt; 31">
                                <img src="./assets/icons/nuage.png" alt="pluie" />
                            </xsl:when>
                            <xsl:when test="(pluie &gt; 20) and (pluie &lt; 61)">
                                <img src="./assets/icons/pluie_legere.png" alt="pluie" />
                            </xsl:when>
                            <xsl:when test="pluie &gt; 60">
                                <img src="./assets/icons/pluie_intense.png" alt="pluie" />
                            </xsl:when>
                        </xsl:choose>
                        <br />
                        <xsl:value-of select="pluie * 100" />
                        <xsl:text> %</xsl:text>
                    </td>
                    <td>
                        <xsl:value-of select="humidite/level" />
                        <xsl:text> %</xsl:text>
                    </td>
                    <td>
                        <xsl:choose>
                            <xsl:when test="risque_neige = 'oui'">
                                <img src="./assets/icons/neige.png" alt="neige" />
                            </xsl:when>
                            <xsl:otherwise>
                                <img src="./assets/icons/pas_de_neige.png" alt="neige" />
                            </xsl:otherwise>
                        </xsl:choose>
                    </td>
                    <td>
                        <!-- A CHERCHER : BONNES CONDITIONS POUR CYCLISME -->
                        <xsl:choose>
                            <xsl:when test="(format-number(temperature/level[2] - 273.15, '.00') &gt; 10) and (risque_neige = 'non') and ((vent_moyen/level) &lt; 20) and (pluie &lt; 31)">
                                <img src="./assets/icons/valide.png" alt="condition" />
                            </xsl:when>
                            <xsl:otherwise>
                                <img src="./assets/icons/non.png" alt="condition" />
                            </xsl:otherwise>
                        </xsl:choose>
                    </td>
                </tr>
            </tbody>
        </table>
    </xsl:template>

</xsl:stylesheet>
