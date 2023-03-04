import createAxios from '/@/utils/axios'
import { demandRecord } from '/@/api/controllerUrls'

export function getSelect() {
    return createAxios({
        url: demandRecord + 'getSelect',
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
