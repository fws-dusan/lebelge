<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexibleShippingImportExportVendor\League\Csv;

/**
 * Defines constants for common BOM sequences.
 */
interface ByteSequence
{
    /**
     *  UTF-8 BOM sequence.
     */
    const BOM_UTF8 = "ï»¿";
    /**
     * UTF-16 BE BOM sequence.
     */
    const BOM_UTF16_BE = "þÿ";
    /**
     * UTF-16 LE BOM sequence.
     */
    const BOM_UTF16_LE = "ÿþ";
    /**
     * UTF-32 BE BOM sequence.
     */
    const BOM_UTF32_BE = "\0\0þÿ";
    /**
     * UTF-32 LE BOM sequence.
     */
    const BOM_UTF32_LE = "ÿþ\0\0";
}
