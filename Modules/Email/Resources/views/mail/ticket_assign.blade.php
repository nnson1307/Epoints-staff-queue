

<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
        <td class="m_-1487730494717908183minwidth" align="center" style="min-width:512px;background-color: #ffffff;">
            <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td>
                        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                            <tbody>
                            <tr>
                                <td align="center">
                                    <table class="m_-1487730494717908183w100" align="center" width="650" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        <tr>
                                            <td class="m_-1487730494717908183hide" align="center" style="padding-top:10px;padding-bottom:15px">
                                                <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                    <tbody>
                                                    <tr> </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="background-color:white">
                                                <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                    <tbody>
                                                    <tr>
                                                        <td style="border-top: 3px solid #f37b20;border-radius:4px 4px 0 0;"> </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="m_-1487730494717908183padt10m m_-1487730494717908183padb10m" align="center" style="padding-top:15px;padding-bottom:15px">
                                                <img width="150px" class="img-fluid" src="{{$logo}}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="background-color:#f5f5f6">
                                                <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                    <tbody>
                                                    <tr>
                                                        <td style="border-top:1px solid #f5f5f6"></td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="background-color:white;padding-top:25px;padding-bottom:0">
                                                <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <h1 class="m_-1487730494717908183h1m" style="font-size:22px;line-height:28px;letter-spacing:-.20px;margin:10px 0 16px 0;font-family:Helvetica Neue,Arial,sans-serif;color: #00489d;text-align: center; text-transform: uppercase;">THÔNG TIN CẢNH BÁO</h1>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <h4 style="margin-left: 100px; font-weight: unset"> ... xin thông báo với {{$operate_name}} là ticket {{$ticket_code}} vừa được tạo do bạn chủ trì. Hãy truy cập vào để phân công người xử lý nhé!</h4>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center" style="background-color:white;padding-top:0;padding-bottom:20px">
                                                            <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                <tbody>
                                                                <tr>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">Mã ticket</h4>
                                                                    </td>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">{{$ticket_code}}</h4>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">Khách hàng</h4>
                                                                    </td>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">{{$customer}}</h4>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">Loại yêu cầu</h4>
                                                                    </td>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">{{$ticket_request_type_name}}</h4>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">Tiêu đề</h4>
                                                                    </td>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">{{$ticket_title}}</h4>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">Độ ưu tiên</h4>
                                                                    </td>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">{{$priority}}</h4>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">Thời gian phát sinh</h4>
                                                                    </td>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">{{$date_issue == '' || $date_issue == '0000-00-00 00:00:00' ? '' : \Carbon\Carbon::parse($date_issue)->format('d/m/Y H:i')}}</h4>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">Thời gian bắt buộc hoàn thành</h4>
                                                                    </td>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">{{$date_expected == '' || $date_expected == '0000-00-00 00:00:00' ? '' : \Carbon\Carbon::parse($date_expected)->format('d/m/Y H:i')}}</h4>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">Queue xử lý</h4>
                                                                    </td>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">{{$queue_name}}</h4>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">Nhân viên chủ trì</h4>
                                                                    </td>
                                                                    <td>
                                                                        <h4 style="margin-left: 70px;">{{$operate_name}}</h4>
                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>

                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <!--                                     -->
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <table class="m_-1487730494717908183w100" align="center" width="650" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        <tr>
                                            <td align="center" style="background-color: #ffffff;padding-top:20px;padding-bottom:20px;">
                                                <table align="center" bgcolor="white" cellpadding="0" cellspacing="0" style="width:100%;">

                                                    <tbody>

                                                    <tr style="padding:20px">

                                                        <td style="padding:0 25px">

                                                            <div style="border-top:1px solid #bdbdbd;padding-top:32px;text-align:center;line-height:14px">
                                                                <div>

                                                                </div>

                                                            </div>

                                                        </td>

                                                    </tr>

                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="background-color:#f5f5f6">
                                                <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                    <tbody>
                                                    <tr>
                                                        <td style="border-top:1px solid #f5f5f6"></td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
