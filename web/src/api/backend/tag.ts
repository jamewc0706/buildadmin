import createAxios from '/@/utils/axios'
import { tag } from '/@/api/controllerUrls'

export function getSelect() {
    return createAxios({
        url: tag + 'getSelect',
        method: 'get',
    })
}