{if $saved.success}
    <div class="successbox">
        {foreach from=$saved.message item=message}
                    <li>{$message}</li>
                {/foreach}
    </div>
{elseif isset($saved.message) && !$saved.success}
    <div class="errorbox">
        {foreach from=$saved.message item=message}
            <li>{$message}</li>
        {/foreach}
        </div>
{/if}


<h2>Hosting Account</h2>
<form action="sitebuilder.php" method="post">
    <input type="hidden" name="action" value="set_hosting_account">
    <input type="hidden" name="service_id" value="{$sb.serviceid}">
    <table width="100%" cellspacing="0" cellpadding="0" class="frame">
        <tr>
            <td>
                <table width="100%" border="0" cellpadding="10" cellspacing="0">
                    <tr>
                        <td class="fieldarea">Account:</td>
                        <td><select name="hostingid" size="1">
                                {foreach from=$services item=service}
                                    <option {if $sb.domain==$service.domain}selected{/if} value="{$service.id}"
                                            selected="selected">{$service.domain}</option>
                                {/foreach}
                            </select>
                            <input type="submit" value="Save">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>
<h2>Custom Settings</h2>
<form action="sitebuilder.php" method="post">
    <input type="hidden" name="action" value="custom_settings">
    <input type="hidden" name="service_id" value="{$sb.serviceid}">
    <table width="100%" cellspacing="0" cellpadding="0" class="frame">
        <tr>
            <td>
                <table width="100%" border="0" cellpadding="10" cellspacing="0">
                    <tr>
                        <td class="fieldarea">FTP Address:</td>
                        <td><input type="text" name="ftp_address" value="{$sb.ftp_address|htmlspecialchars}"></td>
                    </tr>
                    <tr>
                        <td class="fieldarea">FTP Username:</td>
                        <td><input type="text" name="ftp_username" value="{$sb.ftp_username|htmlspecialchars}"></td>
                    </tr>
                    <tr>
                        <td class="fieldarea">FTP Password:</td>
                        <td><input type="text" name="ftp_password" value="{$sb.ftp_password|htmlspecialchars}"></td>
                    </tr>
                    <tr>
                        <td class="fieldarea">FTP Port:</td>
                        <td><input type="text" name="ftp_port" value="{$sb.ftp_port|htmlspecialchars}"></td>
                    </tr>
                    <tr>
                        <td class="fieldarea">FTP WWW Root:</td>
                        <td><input type="text" name="ftp_wwwroot" value="{$sb.ftp_wwwroot|htmlspecialchars}"></td>
                    </tr>
                    <tr>
                        <td class="fieldarea">FTP Mode:</td>
                        <td><select name="ftp_mode">
                                <option {if $sb.ftp_mode=="active"}selected {/if}value="active">Active</option>
                                <option {if $sb.ftp_mode=="passive"}selected {/if} value="passive">Passive</option>
                            </select></td>
                    </tr>
                    <tr>
                        <td class="fieldarea">FTP Protocol:</td>

                        <td>
                            <select name="ftp_protocol">
                                <option {if $sb.ftp_protocol=="1"}selected {/if}value="1">FTP</option>
                                <option {if $sb.ftp_protocol=="2"}selected {/if} value="2">FTPS (TLS/SSL Auth TLS
                                    Explicit)
                                </option>
                                <option {if $sb.ftp_protocol=="3"}selected {/if} value="3">FTPS (TLS/TLS Implicit)
                                </option>
                            </select>
                        </td>


                    </tr>
                    <tr>
                        <td class="fieldarea">Domain:</td>
                        <td><input type="text" name="domain" value="{$sb.domain|htmlspecialchars}"></td>
                    </tr>
                    <tr>
                </table>
            </td>
        </tr>
    </table>
    <div align="center">
        <input type="submit" value="Save">
    </div>
</form>