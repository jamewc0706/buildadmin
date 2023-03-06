import createAxios from '/@/utils/axios'
import { demandRecord, demandCalendar } from '/@/api/controllerUrls'

export function getSelect() {
    return createAxios({
        url: demandRecord + 'getSelect',
        method: 'get',
    })
}

export function getDateSelect(id:any) {
    return createAxios({
        url: demandRecord + 'getDateSelect?id='+ id,
        method: 'get',
    })
}

export function getPersonDemand() {
    return createAxios({
        url: demandCalendar + 'getPersonDemand',
        method: 'get',
    })
}


export function assign(data: anyObj ) {
    return createAxios(
        {
            url: demandRecord + 'assign',
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}
