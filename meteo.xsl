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
                <h1>Projet A Bicyclette</h1>
                <xsl:apply-templates />
            </body>
        </html>
    </xsl:template>

    <xsl:template match="previsions">
        <xsl:apply-templates select="echeance[1]" />
    </xsl:template>

    <xsl:template match="echeance[1]">
        <h3>
            Température :
        </h3>
        <p>
            <xsl:value-of select="format-number(temperature/level[2] - 273.15, '.00')" />
            <xsl:text>°C</xsl:text>
            <!-- K to C : -273,15 -->
        </p>

        <h3>Vent moyen :</h3>
        <p>
            <xsl:value-of select="vent_moyen/level" />
            <xsl:text> km/h</xsl:text>
        </p>

        <h3>Pluie :</h3>
        <p>
            <xsl:value-of select="pluie * 100" />
            <xsl:text> %</xsl:text>
        </p>

        <h3>Humidité :</h3>
        <p>
            <xsl:value-of select="humidite/level" />
            <xsl:text> %</xsl:text>
        </p>

        <h3>Risque de neige :</h3>
        <p>
            <xsl:value-of select="risque_neige" />
        </p>
    </xsl:template>

</xsl:stylesheet>